<?php

namespace Tests\Feature;

use App\Models\DocumentVaultDocument;
use App\Repositories\DocumentVault\DocumentVaultRepository;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class DocumentVaultMerchantMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
            'database.connections.sqlite.foreign_key_constraints' => true,
        ]);
        DB::purge('sqlite');

        // Minimal source fixtures; the vault itself uses the production migration
        // for fresh installs and the historical schema for upgrade regression tests.
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
        });
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
        });
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('merchant_id')->constrained('merchants');
            $table->foreignId('receipt_id')->nullable()->unique()->constrained('receipts');
        });

        DB::table('merchants')->insert([['id' => 1], ['id' => 2]]);
        DB::table('users')->insert([['id' => 8], ['id' => 9]]);
        DB::table('receipts')->insert(['id' => 1, 'uuid' => '550e8400-e29b-41d4-a716-446655440001']);
        DB::table('payslips')->insert([
            'id' => 1,
            'uuid' => '550e8400-e29b-41d4-a716-446655440002',
            'merchant_id' => 1,
            'receipt_id' => 1,
        ]);
    }

    public function test_upgrade_backfills_source_merchants_and_preserves_documents(): void
    {
        $this->legacyVault();
        $this->document();
        $this->document(['user_id' => 9, 'archived_at' => '2026-09-01 00:00:00']);
        $this->document([
            'document_type' => 'RECEIPT',
            'source_uuid' => '550e8400-e29b-41d4-a716-446655440001',
        ]);
        $before = DB::table('document_vault_documents')->orderBy('id')->get();

        $this->repair()->up();

        $this->assertTrue(Schema::hasColumn('document_vault_documents', 'merchant_id'));
        $this->assertTrue(Schema::hasIndex('document_vault_documents', ['user_id', 'merchant_id']));
        $this->assertSame(3, DB::table('document_vault_documents')->count());

        foreach ($before as $row) {
            $after = (array) DB::table('document_vault_documents')->find($row->id);
            $this->assertSame(1, (int) $after['merchant_id']);
            unset($after['merchant_id']);
            $this->assertEquals((array) $row, $after);
        }

        // Nullable FK matches the schema used on fresh installations.
        $foreignKeys = Schema::getForeignKeys('document_vault_documents');
        $merchantKey = collect($foreignKeys)->firstWhere('columns', ['merchant_id']);
        $this->assertNotNull($merchantKey);
        $this->assertSame('merchants', $merchantKey['foreign_table']);
        $this->assertSame('set null', strtolower($merchantKey['on_delete']));
    }

    public function test_document_sync_can_insert_merchant_id_after_upgrade(): void
    {
        $this->legacyVault();
        $this->repair()->up();

        $repository = new DocumentVaultRepository(new DocumentVaultDocument);
        $source = '550e8400-e29b-41d4-a716-446655440002';
        $data = [
            'merchant_id' => 1,
            'title' => 'Payslip - Aug 20, 2026',
            'reference' => 'PAYSLIP-'.$source,
            'document_date' => '2026-08-20 00:00:00',
        ];

        $document = $repository->firstOrCreateForSource(8, 'PAYSLIP', $source, $data);
        $again = $repository->firstOrCreateForSource(8, 'PAYSLIP', $source, $data);

        $this->assertSame($document->id, $again->id);
        $this->assertSame(1, (int) $document->fresh()->merchant_id);
        $this->assertSame(1, DocumentVaultDocument::count());
    }

    public function test_fresh_install_and_repeated_repair_preserve_existing_merchant_assignments(): void
    {
        (require database_path('migrations/2026_09_24_010000_create_document_vault_documents_table.php'))->up();
        $id = $this->document(['merchant_id' => 2]);
        $legacyNull = $this->document(['user_id' => 9]);

        $this->repair()->up();
        $this->repair()->up();
        $this->repair()->down();

        $this->assertSame(2, (int) DB::table('document_vault_documents')->where('id', $id)->value('merchant_id'));
        $this->assertSame(1, (int) DB::table('document_vault_documents')->where('id', $legacyNull)->value('merchant_id'));
        $this->assertSame(2, DB::table('document_vault_documents')->count());
        $this->assertTrue(Schema::hasIndex('document_vault_documents', ['user_id', 'merchant_id']));
    }

    public function test_unmatched_documents_stay_nullable_and_ownership_comes_from_each_source(): void
    {
        $this->legacyVault();
        $missing = $this->document(['source_uuid' => (string) Str::uuid()]);
        $unlinked = (string) Str::uuid();
        DB::table('receipts')->insert(['id' => 2, 'uuid' => $unlinked]);
        $unlinkedReceipt = $this->document(['document_type' => 'RECEIPT', 'source_uuid' => $unlinked]);

        $otherSource = (string) Str::uuid();
        DB::table('payslips')->insert(['uuid' => $otherSource, 'merchant_id' => 2]);
        $otherDocument = $this->document(['source_uuid' => $otherSource]);

        $this->repair()->up();

        $this->assertNull(DB::table('document_vault_documents')->where('id', $missing)->value('merchant_id'));
        $this->assertNull(DB::table('document_vault_documents')->where('id', $unlinkedReceipt)->value('merchant_id'));
        $this->assertSame(2, (int) DB::table('document_vault_documents')->where('id', $otherDocument)->value('merchant_id'));
    }

    public function test_repair_processes_more_than_one_batch_without_skipping_rows(): void
    {
        $this->legacyVault();

        for ($i = 0; $i < 501; $i++) {
            $source = (string) Str::uuid();
            DB::table('payslips')->insert(['uuid' => $source, 'merchant_id' => 1]);
            $this->document(['source_uuid' => $source]);
        }

        $this->repair()->up();

        $this->assertSame(501, DB::table('document_vault_documents')->where('merchant_id', 1)->count());
        $this->assertSame(0, DB::table('document_vault_documents')->whereNull('merchant_id')->count());
    }

    private function repair(): Migration
    {
        return require database_path('migrations/2026_09_25_090000_ensure_merchant_id_on_document_vault_documents.php');
    }

    private function legacyVault(): void
    {
        Schema::create('document_vault_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 30);
            $table->uuid('source_uuid');
            $table->string('title', 150);
            $table->string('reference', 100)->nullable();
            $table->timestamp('document_date');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'document_type', 'source_uuid'], 'vault_document_source_unique');
            $table->index(['user_id', 'archived_at']);
            $table->index(['user_id', 'document_date']);
        });
    }

    private function document(array $overrides = []): int
    {
        return DB::table('document_vault_documents')->insertGetId(array_merge([
            'uuid' => (string) Str::uuid(),
            'user_id' => 8,
            'document_type' => 'PAYSLIP',
            'source_uuid' => '550e8400-e29b-41d4-a716-446655440002',
            'title' => 'Original document title',
            'reference' => 'Original reference',
            'document_date' => '2026-08-20 00:00:00',
            'archived_at' => null,
            'created_at' => '2026-08-20 01:00:00',
            'updated_at' => '2026-08-20 01:00:00',
        ], $overrides));
    }
}
