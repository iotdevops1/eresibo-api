<?php

namespace Tests\Feature;

use App\Models\IntegrationApiKey;
use App\Models\Receipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PusoPayReceiptLookupTest extends TestCase
{
    private const ENDPOINT = '/api/v1/integrations/pusopay/receipts';

    private IntegrationApiKey $legacyKey;

    protected function setUp(): void
    {
        parent::setUp();

        // Only this integration's real migrations, always on an isolated database.
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
            'eresibo.portal_url' => 'https://receipts.example',
        ]);
        DB::purge('sqlite');

        foreach ([
            '2026_09_01_040640_create_receipts_table.php',
            '2026_09_01_040810_create_integration_api_keys_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }

        $this->legacyKey = IntegrationApiKey::create([
            'name' => 'Legacy test integration',
            'key_hash' => hash('sha256', 'legacy-test-key'),
            'environment' => 'sandbox',
            'active' => true,
        ]);

        (require database_path('migrations/2026_09_24_060000_add_scopes_to_integration_api_keys_table.php'))->up();
        Queue::fake();
    }

    public function test_lookup_recovers_the_creation_response_by_both_identifiers(): void
    {
        $key = $this->key(['receipts.create', 'receipts.read']);
        $created = $this->withHeader('X-API-Key', $key)->postJson(self::ENDPOINT, [
            'externalReference' => 'PP+123 & reference/#',
            'amountMinor' => 250000,
            'currency' => 'PHP',
            'transactionType' => 'MERCHANT_PAYMENT',
            'counterpartyLabel' => 'Test merchant',
            'occurredAt' => now()->toISOString(),
        ])->assertCreated();

        $receiptId = $created->json('data.receiptId');
        $before = Receipt::firstOrFail()->getRawOriginal();
        Queue::fake();

        foreach ([
            ['externalReference' => 'PP+123 & reference/#'],
            ['receiptId' => $receiptId],
        ] as $query) {
            $this->getJson(self::ENDPOINT.'?'.http_build_query($query))
                ->assertOk()
                ->assertJsonPath('data.receiptId', $receiptId)
                ->assertJsonPath('data.receiptUrl', $created->json('data.receiptUrl'))
                ->assertJsonPath('data.expiresAt', $created->json('data.expiresAt'))
                ->assertJsonPath('data.status.name', 'CONFIRMED')
                ->assertJsonPath('data.amountMinor', 250000)
                ->assertJsonPath('data.currency', 'PHP')
                ->assertJsonPath('data.isExpired', false);
        }

        $this->assertSame(1, Receipt::count());
        $this->assertSame($before, Receipt::firstOrFail()->getRawOriginal());
        Queue::assertNothingPushed();
    }

    public static function invalidQueries(): array
    {
        return [
            'no identifier' => [[], 'externalReference'],
            'empty reference' => [['externalReference' => ''], 'externalReference'],
            'invalid uuid' => [['receiptId' => '123'], 'receiptId'],
            'long reference' => [['externalReference' => str_repeat('a', 101)], 'externalReference'],
            'array reference' => [['externalReference' => ['PP-1']], 'externalReference'],
            'both identifiers' => [[
                'externalReference' => 'PP-1',
                'receiptId' => '550e8400-e29b-41d4-a716-446655440000',
            ], 'externalReference'],
        ];
    }

    #[DataProvider('invalidQueries')]
    public function test_invalid_lookup_returns_field_errors(array $query, string $field): void
    {
        $this->withHeader('X-API-Key', $this->key())
            ->getJson(self::ENDPOINT.'?'.http_build_query($query))
            ->assertUnprocessable()
            ->assertJsonValidationErrors($field);
    }

    public function test_missing_or_invalid_key_is_unauthenticated(): void
    {
        $this->getJson(self::ENDPOINT.'?externalReference=PP-1')->assertUnauthorized();
        $this->withHeader('X-API-Key', 'wrong-test-key')
            ->getJson(self::ENDPOINT.'?externalReference=PP-1')->assertUnauthorized();
    }

    public function test_inactive_and_expired_keys_are_rejected(): void
    {
        foreach ([
            ['active' => false],
            ['expires_at' => now()->subMinute()],
        ] as $state) {
            $token = $this->key();
            IntegrationApiKey::where('key_hash', hash('sha256', $token))->update($state);
            $this->withHeader('X-API-Key', $token)
                ->getJson(self::ENDPOINT.'?externalReference=PP-1')->assertUnauthorized();
        }
    }

    public function test_scope_is_required_even_with_a_valid_key(): void
    {
        foreach ([[], ['receipts.create'], ['funding.confirm'], ['*']] as $scopes) {
            $this->withHeader('X-API-Key', $this->key($scopes))
                ->getJson(self::ENDPOINT.'?externalReference=PP-1')
                ->assertForbidden()
                ->assertJsonPath('requiredScope', 'receipts.read');
        }
    }

    public function test_null_scopes_do_not_grant_access(): void
    {
        $token = $this->key();
        IntegrationApiKey::where('key_hash', hash('sha256', $token))->update(['scopes' => null]);
        $this->withHeader('X-API-Key', $token)
            ->getJson(self::ENDPOINT.'?externalReference=PP-1')->assertForbidden();
    }

    public function test_receipt_read_key_cannot_create_receipts_or_confirm_funding(): void
    {
        $this->withHeader('X-API-Key', $this->key())
            ->postJson(self::ENDPOINT, [])->assertForbidden()
            ->assertJsonPath('requiredScope', 'receipts.create');

        $this->postJson('/api/v1/integrations/pusopay/fundings/confirm', [])
            ->assertForbidden()->assertJsonPath('requiredScope', 'funding.confirm');
    }

    public function test_receipts_only_key_cannot_access_funding_or_internal_lookup(): void
    {
        $this->withHeader('X-API-Key', $this->key(['receipts.create', 'receipts.read']))
            ->postJson('/api/v1/integrations/pusopay/fundings/confirm', [])->assertForbidden();

        $this->getJson('/api/v1/internal/receipts/test-token')->assertUnauthorized();
    }

    public function test_unknown_non_pusopay_and_deleted_receipts_are_not_disclosed(): void
    {
        $other = $this->receipt(['source_system' => 'ERESIBO']);
        $deleted = $this->receipt();
        $deleted->delete();
        $this->withHeader('X-API-Key', $this->key());

        foreach ([
            ['externalReference' => 'missing'],
            ['receiptId' => (string) Str::uuid()],
            ['externalReference' => $other->external_reference],
            ['receiptId' => $other->uuid],
            ['externalReference' => $deleted->external_reference],
            ['receiptId' => $deleted->uuid],
        ] as $query) {
            $this->getJson(self::ENDPOINT.'?'.http_build_query($query))
                ->assertNotFound()->assertJsonPath('success', false);
        }
    }

    public function test_expired_and_failed_receipts_remain_recoverable_without_renewal(): void
    {
        $receipt = $this->receipt([
            'expires_at' => now()->subDay(),
            'status' => Receipt::STATUS_FAILED,
        ]);
        $before = $receipt->fresh()->getRawOriginal();

        $this->withHeader('X-API-Key', $this->key())
            ->getJson(self::ENDPOINT.'?receiptId='.$receipt->uuid)
            ->assertOk()
            ->assertJsonPath('data.isExpired', true)
            ->assertJsonPath('data.status.name', 'FAILED')
            ->assertJsonPath('data.expiresAt', $receipt->expires_at->toISOString());

        $this->assertSame($before, $receipt->fresh()->getRawOriginal());
        Queue::assertNothingPushed();
    }

    public function test_legacy_keys_preserve_write_access_but_need_explicit_read_grant(): void
    {
        $this->assertSame(['receipts.create', 'funding.confirm'], $this->legacyKey->fresh()->scopes);
        $this->withHeader('X-API-Key', 'legacy-test-key')
            ->getJson(self::ENDPOINT.'?externalReference=PP-1')->assertForbidden();

        $this->artisan('integration:set-key-scopes', [
            'uuid' => $this->legacyKey->uuid,
            '--scope' => ['receipts.create', 'receipts.read'],
        ])->assertSuccessful();

        $this->assertSame(['receipts.create', 'receipts.read'], $this->legacyKey->fresh()->scopes);
        $receipt = $this->receipt();
        $this->getJson(self::ENDPOINT.'?receiptId='.$receipt->uuid)->assertOk();
        $this->postJson('/api/v1/integrations/pusopay/fundings/confirm', [])->assertForbidden();
    }

    public function test_key_commands_require_explicit_valid_scopes(): void
    {
        $this->artisan('integration:generate-api-key', ['name' => 'Missing scopes'])->assertFailed();
        $this->artisan('integration:generate-api-key', [
            'name' => 'Invalid scopes', '--scope' => ['*'],
        ])->assertFailed();
        $this->artisan('integration:set-key-scopes', [
            'uuid' => $this->legacyKey->uuid, '--scope' => ['invalid'],
        ])->assertFailed();
        $this->assertSame(['receipts.create', 'funding.confirm'], $this->legacyKey->fresh()->scopes);

        $this->artisan('integration:generate-api-key', [
            'name' => 'Read only', '--scope' => ['receipts.read'],
        ])->assertSuccessful();
        $this->assertSame(['receipts.read'], IntegrationApiKey::where('name', 'Read only')->firstOrFail()->scopes);
    }

    private function key(array $scopes = ['receipts.read']): string
    {
        $token = 'test-key-'.Str::random(32);
        IntegrationApiKey::create([
            'name' => 'Test integration',
            'key_hash' => hash('sha256', $token),
            'environment' => 'sandbox',
            'active' => true,
            'expires_at' => now()->addDay(),
            'scopes' => $scopes,
        ]);

        return $token;
    }

    private function receipt(array $overrides = []): Receipt
    {
        return Receipt::create(array_merge([
            'source_system' => 'PUSOPAY',
            'external_reference' => 'PP-'.Str::uuid(),
            'amount_minor' => 10000,
            'currency' => 'PHP',
            'transaction_type' => 'TRANSFER',
            'counterparty_label' => 'Test counterparty',
            'occurred_at' => now(),
            'public_token' => Str::random(22),
            'expires_at' => now()->addDays(90),
            'status' => Receipt::STATUS_CONFIRMED,
            'processed_at' => now(),
        ], $overrides));
    }
}
