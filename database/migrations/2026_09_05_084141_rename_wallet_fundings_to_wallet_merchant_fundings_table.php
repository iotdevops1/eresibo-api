<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wallet_fundings')
            && ! Schema::hasTable('wallet_merchant_fundings')) {
            Schema::rename(
                'wallet_fundings',
                'wallet_merchant_fundings'
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('wallet_merchant_fundings')
            && ! Schema::hasTable('wallet_fundings')) {
            Schema::rename(
                'wallet_merchant_fundings',
                'wallet_fundings'
            );
        }
    }
};