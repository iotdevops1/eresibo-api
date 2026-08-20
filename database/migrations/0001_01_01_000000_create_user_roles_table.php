<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();

            // Public Identifier
            $table->uuid('uuid')->unique();

            // Role Information
            $table->string('code', 50)->unique();      // SUPER_ADMIN, ADMIN, CUSTOMER
            $table->string('name', 100);               // Super Administrator, Administrator, Customer
            $table->text('description')->nullable();

            // Status
            $table->boolean('active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};