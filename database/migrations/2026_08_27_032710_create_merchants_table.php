<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchants', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->string('merchant_code', 50)->unique();

            $table->string('business_name', 255);

            $table->string('business_type', 100)->nullable();

            $table->string('email', 255)->nullable();

            $table->string('mobile', 20)->nullable();

            $table->text('address')->nullable();

            $table->unsignedTinyInteger('status')
                ->default(1)
                ->comment(
                    '1=ACTIVE, 2=INACTIVE, 3=SUSPENDED'
                );

            $table->timestamps();

            $table->softDeletes();

            $table->index([
                'status',
                'business_name',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};