<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_api_keys', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->string('name', 100);

            $table->string('key_hash', 64)->unique();

            $table->string('environment', 20)
                ->default('sandbox');

            $table->boolean('active')
                ->default(true);

            $table->timestamp('expires_at')
                ->nullable();

            $table->timestamp('last_used_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'environment',
                'active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_api_keys');
    }
};