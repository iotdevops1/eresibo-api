<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Add merchant_id if it does not exist
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('payroll_batches', 'merchant_id')) {
            Schema::table('payroll_batches', function (Blueprint $table) {
                $table->unsignedBigInteger('merchant_id')
                    ->nullable()
                    ->after('id');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 2. First migrate from employer -> merchant
        |--------------------------------------------------------------------------
        |
        | This works for production records where the existing employer
        | already has a merchant_id.
        |
        */

        if (Schema::hasColumn('payroll_batches', 'employer_id')) {
            DB::statement('
                UPDATE payroll_batches pb
                INNER JOIN users u
                    ON u.id = pb.employer_id
                SET pb.merchant_id = u.merchant_id
                WHERE pb.merchant_id IS NULL
                  AND u.merchant_id IS NOT NULL
            ');
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Recover remaining batches through their employees
        |--------------------------------------------------------------------------
        |
        | This handles legacy data where employer_id points to an old
        | employer account that has no merchant_id.
        |
        | A batch is eligible only when all its employees belong to exactly
        | one merchant.
        |
        */

        DB::statement('
            UPDATE payroll_batches pb
            INNER JOIN (
                SELECT
                    pbi.payroll_batch_id,
                    MIN(e.merchant_id) AS merchant_id
                FROM payroll_batch_items pbi
                INNER JOIN employees e
                    ON e.id = pbi.employee_id
                WHERE e.merchant_id IS NOT NULL
                GROUP BY pbi.payroll_batch_id
                HAVING COUNT(DISTINCT e.merchant_id) = 1
            ) mapped
                ON mapped.payroll_batch_id = pb.id
            SET pb.merchant_id = mapped.merchant_id
            WHERE pb.merchant_id IS NULL
        ');

        /*
        |--------------------------------------------------------------------------
        | 4. Safety check - no active batch may remain unmapped
        |--------------------------------------------------------------------------
        */

        $unmappedBatches = DB::table('payroll_batches')
            ->whereNull('merchant_id')
            ->whereNull('deleted_at')
            ->count();

        if ($unmappedBatches > 0) {
            throw new \RuntimeException(
                "Migration stopped: {$unmappedBatches} active payroll batch(es) could not be assigned to a merchant."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Safety check - merchant cannot be ambiguous
        |--------------------------------------------------------------------------
        */

        $mixedMerchantBatches = DB::table('payroll_batch_items as pbi')
            ->join(
                'employees as e',
                'e.id',
                '=',
                'pbi.employee_id'
            )
            ->whereNotNull('e.merchant_id')
            ->select('pbi.payroll_batch_id')
            ->groupBy('pbi.payroll_batch_id')
            ->havingRaw('COUNT(DISTINCT e.merchant_id) > 1')
            ->count();

        if ($mixedMerchantBatches > 0) {
            throw new \RuntimeException(
                "Migration stopped: {$mixedMerchantBatches} payroll batch(es) contain employees from multiple merchants."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Add merchant foreign key if missing
        |--------------------------------------------------------------------------
        */

        $merchantForeignKeyExists = DB::table(
            'information_schema.KEY_COLUMN_USAGE'
        )
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'payroll_batches')
            ->where('COLUMN_NAME', 'merchant_id')
            ->where('REFERENCED_TABLE_NAME', 'merchants')
            ->where('REFERENCED_COLUMN_NAME', 'id')
            ->exists();

        if (! $merchantForeignKeyExists) {
            Schema::table('payroll_batches', function (Blueprint $table) {
                $table->foreign(
                    'merchant_id',
                    'payroll_batches_merchant_id_foreign'
                )
                    ->references('id')
                    ->on('merchants')
                    ->restrictOnDelete();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 7. Remove old employer foreign key
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn('payroll_batches', 'employer_id')) {

            $employerForeignKeyExists = DB::table(
                'information_schema.KEY_COLUMN_USAGE'
            )
                ->where('TABLE_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', 'payroll_batches')
                ->where('COLUMN_NAME', 'employer_id')
                ->where('REFERENCED_TABLE_NAME', 'users')
                ->exists();

            if ($employerForeignKeyExists) {
                Schema::table('payroll_batches', function (Blueprint $table) {
                    $table->dropForeign(['employer_id']);
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Remove old unique index
            |--------------------------------------------------------------------------
            */

            $oldUniqueIndexExists = DB::table(
                'information_schema.STATISTICS'
            )
                ->where('TABLE_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', 'payroll_batches')
                ->where(
                    'INDEX_NAME',
                    'payroll_batches_employer_id_batch_no_unique'
                )
                ->exists();

            if ($oldUniqueIndexExists) {
                Schema::table('payroll_batches', function (Blueprint $table) {
                    $table->dropIndex(
                        'payroll_batches_employer_id_batch_no_unique'
                    );
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Remove old employer/status index
            |--------------------------------------------------------------------------
            */

            $oldStatusIndexExists = DB::table(
                'information_schema.STATISTICS'
            )
                ->where('TABLE_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', 'payroll_batches')
                ->where(
                    'INDEX_NAME',
                    'payroll_batches_employer_id_status_index'
                )
                ->exists();

            if ($oldStatusIndexExists) {
                Schema::table('payroll_batches', function (Blueprint $table) {
                    $table->dropIndex(
                        'payroll_batches_employer_id_status_index'
                    );
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Remove employer_id
            |--------------------------------------------------------------------------
            */

            Schema::table('payroll_batches', function (Blueprint $table) {
                $table->dropColumn('employer_id');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 8. Add Merchant indexes
        |--------------------------------------------------------------------------
        */

        $uniqueIndexExists = DB::table(
            'information_schema.STATISTICS'
        )
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'payroll_batches')
            ->where(
                'INDEX_NAME',
                'payroll_batches_merchant_id_batch_no_unique'
            )
            ->exists();

        if (! $uniqueIndexExists) {
            Schema::table('payroll_batches', function (Blueprint $table) {
                $table->unique(
                    ['merchant_id', 'batch_no'],
                    'payroll_batches_merchant_id_batch_no_unique'
                );
            });
        }

        $statusIndexExists = DB::table(
            'information_schema.STATISTICS'
        )
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'payroll_batches')
            ->where(
                'INDEX_NAME',
                'payroll_batches_merchant_id_status_index'
            )
            ->exists();

        if (! $statusIndexExists) {
            Schema::table('payroll_batches', function (Blueprint $table) {
                $table->index(
                    ['merchant_id', 'status'],
                    'payroll_batches_merchant_id_status_index'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 9. Make merchant_id required
        |--------------------------------------------------------------------------
        */

        Schema::table('payroll_batches', function (Blueprint $table) {
            $table->unsignedBigInteger('merchant_id')
                ->nullable(false)
                ->change();
        });
    }

    public function down(): void
    {
        throw new \RuntimeException(
            'This migration cannot be safely rolled back because payroll batch ownership has moved from employer to merchant.'
        );
    }
};