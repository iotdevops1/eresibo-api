<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            /*
            |--------------------------------------------------------------------------
            | Wallet Owner
            |--------------------------------------------------------------------------
            |
            | Examples:
            | owner_type = merchant
            | owner_id   = merchants.id
            |
            | owner_type = user
            | owner_id   = users.id
            |
            */

            $table->string('owner_type', 30);
            $table->unsignedBigInteger('owner_id');

            /*
            |--------------------------------------------------------------------------
            | Currency
            |--------------------------------------------------------------------------
            */

            $table->char('currency', 3)->default('PHP');

            /*
            |--------------------------------------------------------------------------
            | Balance
            |--------------------------------------------------------------------------
            |
            | Minor units are the authoritative financial value.
            |
            | Example:
            | ₱1,500.75 = 150075
            |
            */

            $table->unsignedBigInteger('balance_minor_units')
                ->default(0);

            /*
            | Major units are stored for convenient API/display use.
            |
            | Minor units remain the source of truth.
            */

            $table->decimal('balance_major_units', 20, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->unsignedTinyInteger('status')
                ->default(1);

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['owner_type', 'owner_id'],
                'wallets_owner_index'
            );

            $table->index(
                ['status', 'currency'],
                'wallets_status_currency_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};