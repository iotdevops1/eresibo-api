<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            /*
            |--------------------------------------------------------------------------
            | Integration
            |--------------------------------------------------------------------------
            */

            $table->string('source_system', 30)
                ->default('PUSOPAY');

            $table->string('external_reference', 100)
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | Transaction
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('amount_minor');

            $table->char('currency', 3);

            $table->string('transaction_type', 30);

            $table->string('counterparty_label', 255)
                ->nullable();

            $table->timestamp('occurred_at');

            /*
            |--------------------------------------------------------------------------
            | Public Receipt
            |--------------------------------------------------------------------------
            */

            /*
            | 128-bit random token encoded as URL-safe text.
            | 16 random bytes => 128 bits.
            */

            $table->string('public_token', 22)
                ->unique();

            $table->timestamp('expires_at');

            /*
            |--------------------------------------------------------------------------
            | Processing
            |--------------------------------------------------------------------------
            */

            $table->unsignedTinyInteger('status')
                ->default(1)
                ->comment('1=CONFIRMED, 2=FAILED');

            $table->timestamp('processed_at')
                ->nullable();

            $table->text('failure_reason')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'source_system',
                'external_reference',
            ]);

            $table->index([
                'status',
                'expires_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};