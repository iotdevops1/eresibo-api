<?php

namespace Tests\Feature;

use App\Models\Receipt;
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

    public function test_payslip_reference_reports_status_without_private_payroll_fields(): void
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
        $this->getJson(self::URL.'?document=payslip-'.strtoupper($uuid))->assertOk();
        foreach ([0, 3, 4] as $status) {
            DB::table('payslips')->update(['status' => $status]);
            $this->getJson($url)->assertNotFound()->assertJsonPath('data', null);
        }
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
