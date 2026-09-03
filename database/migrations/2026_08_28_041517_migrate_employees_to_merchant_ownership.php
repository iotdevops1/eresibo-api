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

        if (! Schema::hasColumn('employees', 'merchant_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->unsignedBigInteger('merchant_id')
                    ->nullable()
                    ->after('id');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Migrate existing employer ownership
        |--------------------------------------------------------------------------
        |
        | This only runs when employer_id still exists.
        |
        */

        if (Schema::hasColumn('employees', 'employer_id')) {
            DB::statement('
                UPDATE employees e
                INNER JOIN users u
                    ON u.id = e.employer_id
                SET e.merchant_id = u.merchant_id
                WHERE e.merchant_id IS NULL
                  AND e.employer_id IS NOT NULL
                  AND u.merchant_id IS NOT NULL
            ');
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Safety check
        |--------------------------------------------------------------------------
        |
        | Never remove the old employer relationship if an active employee
        | cannot be assigned to a merchant.
        |
        */

        $unmappedEmployees = DB::table('employees')
            ->whereNull('merchant_id')
            ->whereNull('deleted_at')
            ->count();

        if ($unmappedEmployees > 0) {
            throw new \RuntimeException(
                "Migration stopped: {$unmappedEmployees} active employee(s) do not have a merchant."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Add merchant_id foreign key if it does not exist
        |--------------------------------------------------------------------------
        */

        $merchantForeignKeyExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'employees')
            ->where('COLUMN_NAME', 'merchant_id')
            ->where('REFERENCED_TABLE_NAME', 'merchants')
            ->where('REFERENCED_COLUMN_NAME', 'id')
            ->exists();

        if (! $merchantForeignKeyExists) {
            Schema::table('employees', function (Blueprint $table) {
                $table->foreign('merchant_id', 'employees_merchant_id_foreign')
                    ->references('id')
                    ->on('merchants')
                    ->restrictOnDelete();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Add merchant/status index if it does not exist
        |--------------------------------------------------------------------------
        */

        $merchantStatusIndexExists = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'employees')
            ->where('INDEX_NAME', 'employees_merchant_id_status_index')
            ->exists();

        if (! $merchantStatusIndexExists) {
            Schema::table('employees', function (Blueprint $table) {
                $table->index(
                    ['merchant_id', 'status'],
                    'employees_merchant_id_status_index'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Remove old employer_id if it still exists
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn('employees', 'employer_id')) {

            /*
            | Check if the old employer/status index exists.
            */

            $oldIndexExists = DB::table('information_schema.STATISTICS')
                ->where('TABLE_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', 'employees')
                ->where('INDEX_NAME', 'employees_employer_id_status_index')
                ->exists();

            /*
            | Remove foreign key if it exists.
            */

            $oldForeignKeyExists = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('TABLE_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', 'employees')
                ->where('COLUMN_NAME', 'employer_id')
                ->where('REFERENCED_TABLE_NAME', 'users')
                ->exists();

            if ($oldForeignKeyExists) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->dropForeign(['employer_id']);
                });
            }

            if ($oldIndexExists) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->dropIndex('employees_employer_id_status_index');
                });
            }

            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('employer_id');
            });
        }
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | This migration is intentionally irreversible.
        |--------------------------------------------------------------------------
        |
        | Once employees belong to merchants, there is no deterministic way
        | to restore the original employer when multiple employer accounts
        | belong to the same merchant.
        |
        */

        throw new \RuntimeException(
            'This migration cannot be safely rolled back because employee ownership has been moved from employer to merchant.'
        );
    }
};