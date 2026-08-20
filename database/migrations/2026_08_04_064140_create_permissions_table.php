<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {

            $table->id();

            // Public Identifier
            $table->uuid('uuid')->unique();

            // Permission Information
            $table->string('module', 50);
            $table->string('code', 100)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();

            // Status
            $table->boolean('active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('module');
            $table->index('active');
            $table->index(['module', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};