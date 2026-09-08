<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_entries', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            /*
            |--------------------------------------------------------------------------
            | Parent Wallet Transaction
            |--------------------------------------------------------------------------
            */

            $table->foreignId('wallet_transaction_id')
                ->constrained('wallet_transactions')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Wallet
            |--------------------------------------------------------------------------
            */

            $table->foreignId('wallet_id')
                ->constrained('wallets')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Entry Type
            |--------------------------------------------------------------------------
            |
            | CREDIT = adds funds to wallet
            | DEBIT  = removes funds from wallet
            |
            */

            $table->string('entry_type', 10);

            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('amount_minor_units');

            $table->decimal('amount_major_units', 20, 2);

            /*
            |--------------------------------------------------------------------------
            | Balance Snapshot
            |--------------------------------------------------------------------------
            |
            | Balance immediately before and after this entry.
            |
            */

            $table->unsignedBigInteger('balance_before_minor_units');

            $table->decimal('balance_before_major_units', 20, 2);

            $table->unsignedBigInteger('balance_after_minor_units');

            $table->decimal('balance_after_major_units', 20, 2);

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['wallet_id', 'created_at'],
                'wallet_entries_wallet_created_index'
            );

            $table->index(
                ['wallet_transaction_id'],
                'wallet_entries_transaction_index'
            );

            $table->index(
                ['entry_type'],
                'wallet_entries_entry_type_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_entries');
    }
};