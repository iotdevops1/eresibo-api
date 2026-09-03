<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('employees', 'user_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('merchant_id')
                    ->constrained('users')
                    ->nullOnDelete();

                $table->unique('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('employees', 'user_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropUnique('employees_user_id_unique');
                $table->dropColumn('user_id');
            });
        }
    }
};