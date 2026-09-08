<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_fundings', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            /*
            |--------------------------------------------------------------------------
            | Destination Wallet
            |--------------------------------------------------------------------------
            */

            $table->foreignId('wallet_id')
                ->constrained('wallets')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Funding Provider
            |--------------------------------------------------------------------------
            |
            | Examples:
            | PUSOPAY
            | MAYA
            | BANK
            | MANUAL
            |
            */

            $table->string('provider', 50);

            /*
            |--------------------------------------------------------------------------
            | External Reference
            |--------------------------------------------------------------------------
            |
            | The provider's transaction/reference ID.
            |
            | This must be unique per provider to prevent
            | duplicate credits.
            |
            */

            $table->string('external_reference', 150);

            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('amount_minor_units');

            $table->decimal('amount_major_units', 20, 2);

            $table->char('currency', 3);

            /*
            |--------------------------------------------------------------------------
            | Funding Status
            |--------------------------------------------------------------------------
            */

            $table->unsignedTinyInteger('status')->default(1);

            /*
            |--------------------------------------------------------------------------
            | Provider Data
            |--------------------------------------------------------------------------
            */

            $table->json('metadata')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Confirmation
            |--------------------------------------------------------------------------
            */

            $table->timestamp('confirmed_at')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Constraints / Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique(
                ['provider', 'external_reference'],
                'wallet_fundings_provider_reference_unique'
            );

            $table->index(
                ['wallet_id', 'status'],
                'wallet_fundings_wallet_status_index'
            );

            $table->index(
                ['provider', 'status'],
                'wallet_fundings_provider_status_index'
            );

            $table->index(
                'confirmed_at',
                'wallet_fundings_confirmed_at_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_fundings');
    }
};