<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE users SET status =
                CASE status
                    WHEN 'ACTIVE' THEN 1
                    WHEN 'INACTIVE' THEN 2
                    WHEN 'SUSPENDED' THEN 3
                    WHEN 'LOCKED' THEN 4
                    WHEN 'DELETED' THEN 5
                    ELSE 1
                END
        ");

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('status')
                ->default(1)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')
                ->default('ACTIVE')
                ->change();
        });

        DB::statement("
            UPDATE users SET status =
                CASE status
                    WHEN '1' THEN 'ACTIVE'
                    WHEN '2' THEN 'INACTIVE'
                    WHEN '3' THEN 'SUSPENDED'
                    WHEN '4' THEN 'LOCKED'
                    WHEN '5' THEN 'DELETED'
                    ELSE 'ACTIVE'
                END
        ");
    }
};