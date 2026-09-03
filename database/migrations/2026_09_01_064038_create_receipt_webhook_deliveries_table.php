<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_webhook_deliveries', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('receipt_id')
                ->constrained('receipts')
                ->restrictOnDelete();

            $table->string('endpoint', 500);

            $table->unsignedTinyInteger('status')
                ->default(1)
                ->comment('1=PENDING, 2=SENT, 3=FAILED');

            $table->unsignedInteger('attempts')
                ->default(0);

            $table->unsignedSmallInteger('last_http_status')
                ->nullable();

            $table->text('last_response')
                ->nullable();

            $table->timestamp('last_attempted_at')
                ->nullable();

            $table->timestamp('delivered_at')
                ->nullable();

            $table->timestamp('next_attempt_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'receipt_id',
                'status',
            ]);

            $table->index([
                'status',
                'next_attempt_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_webhook_deliveries');
    }
};