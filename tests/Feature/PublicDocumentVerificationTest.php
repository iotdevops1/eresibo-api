<?php

namespace Tests\Feature;

use App\Http\Resources\PayslipResource;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\Receipt;
use App\Services\Payslip\PayslipService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicDocumentVerificationTest extends TestCase
{
    private const URL = '/api/v1/public/documents/verify';

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array']);
        DB::purge('sqlite');
        Schema::disableForeignKeyConstraints();
        (require database_path('migrations/2026_09_01_040640_create_receipts_table.php'))->up();
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->softDeletes();
        });
        (require database_path('migrations/2026_09_17_000000_create_payslips_tables.php'))->up();
    }

    private function receipt(array $overrides = []): Receipt
    {
        return Receipt::create(array_replace([
            'source_system' => 'PUSOPAY', 'external_reference' => 'PP+123 & reference/#',
            'amount_minor' => 15000, 'currency' => 'PHP', 'transaction_type' => 'MERCHANT_PAYMENT',
            'counterparty_label' => 'Private label', 'occurred_at' => now(),
            'public_token' => 'abcdefghijklmnopqrstuv', 'expires_at' => now()->addDay(),
            'status' => Receipt::STATUS_CONFIRMED,
        ], $overrides));
    }

    public function test_integrated_receipt_is_publicly_verifiable_by_all_supported_identifiers(): void
    {
        $receipt = $this->receipt();
        foreach ([$receipt->external_reference, $receipt->uuid, $receipt->public_token, ' '.strtoupper($receipt->uuid).' '] as $id) {
            $response = $this->getJson(self::URL.'?'.http_build_query(['document' => $id]))
                ->assertOk()->assertJsonPath('state', 'verified')
                ->assertJsonPath('success', true)
                ->assertJsonPath('message', 'Document verified successfully.')
                ->assertJsonPath('data.amount_minor', 15000)
                ->assertJsonPath('data.source_system', 'PUSOPAY')
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            $this->assertEqualsCanonicalizing([
                'document_type', 'reference', 'receipt_id', 'source_system', 'status',
                'issued_at', 'amount_minor', 'currency', 'transaction_type', 'expires_at',
            ], array_keys($response->json('data')));
            $this->assertArrayNotHasKey('public_token', $response->json('data'));
            $this->assertArrayNotHasKey('counterparty_label', $response->json('data'));
        }
    }

    public function test_expired_and_failed_receipts_are_not_verified(): void
    {
        $receipt = $this->receipt();
        $receipt->update(['expires_at' => now()->subDay()]);
        $url = self::URL.'?document='.$receipt->uuid;
        $this->getJson($url)->assertStatus(410)->assertJson([
            'success' => false, 'message' => 'Document verification link has expired.',
            'state' => 'expired', 'data' => null, 'errors' => null,
        ])->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $receipt->update(['status' => Receipt::STATUS_FAILED]);
        $this->getJson($url)->assertNotFound()->assertJson([
            'success' => false, 'message' => 'Document not found.',
            'state' => 'not_found', 'data' => null, 'errors' => null,
        ]);
        $receipt->delete();
        $this->getJson($url)->assertNotFound();
    }

    private function payslip(): Payslip
    {
        DB::table('merchants')->insert(['id' => 1, 'business_name' => 'Test Merchant']);
        $uuid = 'c3cabfd5-be33-4558-8e6b-b2a730e11a71';
        DB::table('payslips')->insert([
            'uuid' => $uuid, 'merchant_id' => 1, 'employee_id' => 1, 'issued_by_user_id' => 1,
            'pay_period_start' => '2026-09-01', 'pay_period_end' => '2026-09-15', 'pay_date' => '2026-09-17',
            'gross_amount_minor_units' => 15000, 'net_amount_minor_units' => 15000,
            'gross_amount_major_units' => 150, 'net_amount_major_units' => 150,
            'status' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return Payslip::where('uuid', $uuid)->firstOrFail();
    }

    public function test_payslip_reference_reports_status_without_private_payroll_fields(): void
    {
        $payslip = $this->payslip();
        $uuid = $payslip->uuid;
        $url = self::URL.'?document=PAYSLIP-'.$uuid;
        $response = $this->getJson($url)->assertOk()->assertJsonPath('data.status', 'Pending acknowledgement');
        $this->assertEqualsCanonicalizing([
            'document_type', 'reference', 'source_system', 'issuer', 'status',
            'issued_at', 'pay_date', 'amount_minor', 'currency', 'acknowledged_at',
        ], array_keys($response->json('data')));
        $this->assertArrayNotHasKey('employee_id', $response->json('data'));
        $this->assertArrayNotHasKey('deduction_amount_minor_units', $response->json('data'));
        DB::table('payslips')->update(['status' => 2, 'acknowledged_at' => now()]);
        $this->getJson($url)->assertOk()->assertJsonPath('data.status', 'Acknowledged')
            ->assertJsonPath('data.amount_minor', 15000);
        $this->getJson(self::URL.'?document='.$uuid)->assertOk()
            ->assertJsonPath('data.status', 'Acknowledged')->assertJsonPath('data.reference', $payslip->reference);
        $this->getJson(self::URL.'?document=payslip-'.strtoupper($uuid))->assertOk();
        foreach ([0, 3, 4] as $status) {
            DB::table('payslips')->update(['status' => $status]);
            $this->getJson($url)->assertNotFound()->assertJsonPath('data', null);
            $this->getJson(self::URL.'?document='.$uuid)->assertNotFound()->assertJsonPath('data', null);
        }
    }

    public function test_pending_payslip_is_searchable_by_reference_and_legacy_uuid_without_any_writes(): void
    {
        $payslip = $this->payslip();
        $before = $payslip->getRawOriginal();
        DB::enableQueryLog();

        foreach ([$payslip->reference, $payslip->uuid, ' '.strtoupper($payslip->uuid).' '] as $reference) {
            $this->getJson(self::URL.'?'.http_build_query(['document' => $reference]))
                ->assertOk()->assertJsonPath('state', 'verified')
                ->assertJsonPath('data.document_type', 'Payslip')
                ->assertJsonPath('data.reference', $payslip->reference)
                ->assertJsonPath('data.status', 'Pending acknowledgement')
                ->assertJsonPath('data.acknowledged_at', null);
        }

        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        foreach ($queries as $query) {
            $this->assertMatchesRegularExpression('/^select\b/i', $query['query']);
        }
        $this->assertSame($before, $payslip->refresh()->getRawOriginal());
        $this->assertDatabaseCount('receipts', 0);
        $this->assertNull($payslip->wallet_transaction_id);

        $this->postJson(self::URL, ['document' => $payslip->reference])->assertStatus(405);
        $this->postJson('/api/employee/payslips/'.$payslip->uuid.'/acknowledge')->assertUnauthorized();
    }

    public function test_payslip_creation_response_has_a_stable_reference_before_a_receipt_exists(): void
    {
        config(['eresibo.portal_url' => 'https://portal.example.test/']);
        DB::table('merchants')->insert(['id' => 1, 'business_name' => 'Test Merchant']);
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('merchant_id');
            $table->unsignedBigInteger('user_id');
            $table->string('employee_no');
            $table->string('first_name');
            $table->string('last_name');
            $table->unsignedTinyInteger('status');
            $table->timestamps();
            $table->softDeletes();
        });
        Employee::create([
            'merchant_id' => 1, 'user_id' => 8, 'employee_no' => 'EMP-1',
            'first_name' => 'Test', 'last_name' => 'Employee', 'status' => Employee::STATUS_ACTIVE,
        ]);
        $payslip = app(PayslipService::class)->create(1, 9, [
            'employee_no' => 'EMP-1', 'pay_period_start' => '2026-09-01',
            'pay_period_end' => '2026-09-15', 'pay_date' => '2026-09-17',
            'earnings' => [['description' => 'Basic pay', 'amount_minor_units' => 15000]],
            'deductions' => [],
        ]);
        $pending = (new PayslipResource($payslip))->resolve();

        $this->assertSame('PAYSLIP-'.$payslip->uuid, $pending['reference']);
        $this->assertSame('https://portal.example.test/verify?document='.$pending['reference'], $pending['verification_url']);
        $this->assertSame('PENDING_ACKNOWLEDGEMENT', $pending['status']);
        $this->assertNull($pending['receipt']);
        $this->assertNull($pending['payout']['wallet_transaction_uuid']);
        $this->getJson(self::URL.'?document='.$pending['reference'])->assertOk()
            ->assertJsonPath('data.reference', $pending['reference']);

        // Reference does not depend on receipt generation or acknowledgement state.
        $payslip->status = Payslip::STATUS_ACKNOWLEDGED;
        $payslip->acknowledged_at = now();
        $acknowledged = (new PayslipResource($payslip))->resolve();
        $this->assertSame($pending['reference'], $acknowledged['reference']);
        $this->assertSame($pending['verification_url'], $acknowledged['verification_url']);
    }

    public function test_ambiguous_plain_uuid_is_rejected_but_prefixed_payslip_remains_verifiable(): void
    {
        $payslip = $this->payslip();
        $receipt = $this->receipt(['external_reference' => $payslip->uuid]);

        $this->getJson(self::URL.'?document='.$payslip->uuid)->assertNotFound()->assertJsonPath('data', null);
        $this->getJson(self::URL.'?document='.$payslip->reference)->assertOk()->assertJsonPath('data.document_type', 'Payslip');

        $receipt->update(['external_reference' => 'ANOTHER-REFERENCE', 'uuid' => $payslip->uuid]);
        $this->getJson(self::URL.'?document='.$payslip->uuid)->assertNotFound()->assertJsonPath('data', null);
        $this->getJson(self::URL.'?document='.$payslip->reference)->assertOk()->assertJsonPath('data.document_type', 'Payslip');
    }

    public function test_expired_receipt_does_not_hide_the_independent_payslip_reference(): void
    {
        $payslip = $this->payslip();
        $receipt = $this->receipt(['external_reference' => $payslip->reference, 'expires_at' => now()->subDay()]);

        $this->getJson(self::URL.'?document='.$receipt->uuid)->assertStatus(410);
        $this->getJson(self::URL.'?document='.$payslip->reference)->assertOk()
            ->assertJsonPath('data.status', 'Pending acknowledgement');
        $this->getJson(self::URL.'?document='.$payslip->uuid)->assertOk()
            ->assertJsonPath('data.reference', $payslip->reference);
    }

    public function test_missing_and_invalid_references_do_not_verify(): void
    {
        $this->getJson(self::URL.'?document=missing')->assertNotFound();
        $this->getJson(self::URL.'?document=PAYSLIP-c3cabfd5-be33-4558-8e6b-b2a730e11a71')->assertNotFound();
        $this->getJson(self::URL.'?document[]=invalid')->assertUnprocessable();
        $this->getJson(self::URL.'?document='.str_repeat('a', 101))->assertUnprocessable();
        $this->getJson(self::URL)->assertUnprocessable()->assertJsonValidationErrors('document')
            ->assertJsonPath('success', false)->assertJsonPath('message', 'The given data was invalid.');
        $this->getJson(self::URL.'?document=%20%20')->assertUnprocessable()->assertJsonValidationErrors('document');
    }

    public function test_colliding_receipt_identifiers_are_rejected(): void
    {
        $receipt = $this->receipt();
        $other = $this->receipt([
            'external_reference' => $receipt->public_token,
            'public_token' => 'zyxwvutsrqponmlkjihgfe',
        ]);

        $this->getJson(self::URL.'?document='.$receipt->public_token)
            ->assertNotFound()->assertJsonPath('state', 'not_found')->assertJsonPath('data', null);

        $other->update(['external_reference' => $receipt->uuid]);
        $this->getJson(self::URL.'?document='.$receipt->uuid)
            ->assertNotFound()->assertJsonPath('state', 'not_found')->assertJsonPath('data', null);
    }

    public function test_verification_is_read_only_and_does_not_modify_a_receipt(): void
    {
        $receipt = $this->receipt();
        $before = $receipt->refresh()->getRawOriginal();

        $this->getJson(self::URL.'?document='.$receipt->uuid)->assertOk();

        $this->assertSame($before, $receipt->refresh()->getRawOriginal());
        $this->assertSame(1, Receipt::query()->count());
    }

    public function test_public_verification_is_limited_to_sixty_requests_per_minute_per_ip(): void
    {
        for ($attempt = 0; $attempt < 60; $attempt++) {
            $this->getJson(self::URL.'?document=missing')->assertNotFound();
        }

        $this->getJson(self::URL.'?document=missing')->assertStatus(429)
            ->assertJsonPath('success', false)->assertHeader('Retry-After');

        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])
            ->getJson(self::URL.'?document=missing')->assertNotFound();
    }
}
