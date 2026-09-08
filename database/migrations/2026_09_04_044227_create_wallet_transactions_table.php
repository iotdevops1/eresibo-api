<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            /*
            |--------------------------------------------------------------------------
            | Transaction Reference
            |--------------------------------------------------------------------------
            |
            | Public/business reference for this wallet transaction.
            |
            */

            $table->string('reference', 100)->unique();

            /*
            |--------------------------------------------------------------------------
            | Transaction Type
            |--------------------------------------------------------------------------
            */

            $table->string('type', 50);

            /*
            |--------------------------------------------------------------------------
            | Transaction Status
            |--------------------------------------------------------------------------
            */

            $table->unsignedTinyInteger('status')->default(1);

            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('amount_minor_units');

            $table->decimal('amount_major_units', 20, 2);

            $table->char('currency', 3)->default('PHP');

            /*
            |--------------------------------------------------------------------------
            | Wallet Movement
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('from_wallet_id')->nullable();

            $table->unsignedBigInteger('to_wallet_id')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Description / Metadata
            |--------------------------------------------------------------------------
            */

            $table->string('description', 255)->nullable();

            $table->json('metadata')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Completion
            |--------------------------------------------------------------------------
            */

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */

            $table->foreign('from_wallet_id')
                ->references('id')
                ->on('wallets')
                ->nullOnDelete();

            $table->foreign('to_wallet_id')
                ->references('id')
                ->on('wallets')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['type', 'status'],
                'wallet_transactions_type_status_index'
            );

            $table->index(
                'from_wallet_id',
                'wallet_transactions_from_wallet_id_index'
            );

            $table->index(
                'to_wallet_id',
                'wallet_transactions_to_wallet_id_index'
            );

            $table->index(
                'completed_at',
                'wallet_transactions_completed_at_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};