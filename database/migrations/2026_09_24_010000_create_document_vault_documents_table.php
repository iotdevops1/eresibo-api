<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_vault_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('merchant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 30);
            $table->uuid('source_uuid');
            $table->string('title', 150);
            $table->string('reference', 100)->nullable();
            $table->timestamp('document_date');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['user_id', 'document_type', 'source_uuid'],
                'vault_document_source_unique'
            );
            $table->index(['user_id', 'merchant_id']);
            $table->index(['user_id', 'archived_at']);
            $table->index(['user_id', 'document_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_vault_documents');
    }
};
