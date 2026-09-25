<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Earlier deployments ran the vault migration before merchant_id was added.
        if (! Schema::hasColumn('document_vault_documents', 'merchant_id')) {
            Schema::table('document_vault_documents', function (Blueprint $table) {
                $table->foreignId('merchant_id')->nullable()
                    ->constrained('merchants')->nullOnDelete();
            });
        }

        if (! Schema::hasIndex('document_vault_documents', ['user_id', 'merchant_id'])) {
            Schema::table('document_vault_documents', function (Blueprint $table) {
                $table->index(['user_id', 'merchant_id']);
            });
        }

        // Derive ownership from the source document, not the vault viewer (who
        // may be a platform administrator). Preserve existing assignments.
        DB::table('document_vault_documents')
            ->whereNull('merchant_id')
            ->select(['id', 'document_type', 'source_uuid'])
            ->chunkById(500, function (Collection $documents) {
                $payslipMerchants = DB::table('payslips')
                    ->join('merchants', 'merchants.id', '=', 'payslips.merchant_id')
                    ->whereIn('payslips.uuid', $documents->where('document_type', 'PAYSLIP')->pluck('source_uuid'))
                    ->pluck('payslips.merchant_id', 'payslips.uuid');

                $receiptMerchants = DB::table('receipts')
                    ->join('payslips', 'payslips.receipt_id', '=', 'receipts.id')
                    ->join('merchants', 'merchants.id', '=', 'payslips.merchant_id')
                    ->whereIn('receipts.uuid', $documents->where('document_type', 'RECEIPT')->pluck('source_uuid'))
                    ->pluck('payslips.merchant_id', 'receipts.uuid');

                $assignments = [];

                foreach ($documents as $document) {
                    $merchantId = match ($document->document_type) {
                        'PAYSLIP' => $payslipMerchants->get($document->source_uuid),
                        'RECEIPT' => $receiptMerchants->get($document->source_uuid),
                        default => null,
                    };

                    // Keep unmatched sources nullable; do not guess ownership.
                    if ($merchantId !== null) {
                        $assignments[$merchantId][] = $document->id;
                    }
                }

                foreach ($assignments as $merchantId => $ids) {
                    DB::table('document_vault_documents')
                        ->whereIn('id', $ids)
                        ->whereNull('merchant_id')
                        ->update(['merchant_id' => $merchantId]);
                }
            });
    }

    public function down(): void
    {
        // This compatibility repair is intentionally retained on rollback.
        // Fresh installs already own this column in the create-table migration;
        // dropping it here would break them and discard recovered associations.
    }
};
