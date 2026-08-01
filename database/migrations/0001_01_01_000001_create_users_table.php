<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // Public Identifier
            $table->uuid('uuid')->unique();
            // Role
           $table->foreignId('role_id')
            ->constrained('user_roles')
            ->cascadeOnUpdate()
            ->restrictOnDelete();
            // Personal Information
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile', 20)->nullable();
            // Authentication
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            // Account Status
            $table->enum('status', [
                'ACTIVE',
                'INACTIVE',
                'SUSPENDED',
                'LOCKED'
            ])->default('ACTIVE');
            // Login Tracking
            $table->boolean('is_login')->default(false);
            $table->unsignedTinyInteger('login_attempt')->default(0);
            $table->boolean('is_lock')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};