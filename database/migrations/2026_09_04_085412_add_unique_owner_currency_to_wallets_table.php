<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(
            Schema::getIndexes('wallets')
        );

        $exists = $indexes->contains(
            fn (array $index) =>
                $index['name'] === 'wallets_owner_currency_unique'
        );

        if (! $exists) {
            Schema::table('wallets', function (Blueprint $table) {
                $table->unique(
                    ['owner_type', 'owner_id', 'currency'],
                    'wallets_owner_currency_unique'
                );
            });
        }
    }

    public function down(): void
    {
        $indexes = collect(
            Schema::getIndexes('wallets')
        );

        $exists = $indexes->contains(
            fn (array $index) =>
                $index['name'] === 'wallets_owner_currency_unique'
        );

        if ($exists) {
            Schema::table('wallets', function (Blueprint $table) {
                $table->dropUnique(
                    'wallets_owner_currency_unique'
                );
            });
        }
    }
};