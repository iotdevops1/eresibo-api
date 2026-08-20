<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            /*
            |--------------------------------------------------------------------------
            | Employer
            |--------------------------------------------------------------------------
            */

            $table->foreignId('employer_id')
                ->constrained('users')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Employee Information
            |--------------------------------------------------------------------------
            */

            $table->string('employee_no', 50);

            $table->string('first_name', 100);

            $table->string('middle_name', 100)
                ->nullable();

            $table->string('last_name', 100);

            $table->string('email', 255)
                ->nullable();

            $table->string('mobile', 20)
                ->nullable();

            $table->string('position', 150)
                ->nullable();

            $table->string('department', 150)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | PusoPay
            |--------------------------------------------------------------------------
            */

            $table->string('pusopay_wallet_id', 100)
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | Employment
            |--------------------------------------------------------------------------
            */

            $table->unsignedTinyInteger('status')
                ->default(1)
                ->comment(
                    '1=ACTIVE, 2=INACTIVE, 3=SUSPENDED, 4=TERMINATED'
                );

            $table->date('hired_at')
                ->nullable();

            $table->date('terminated_at')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'employer_id',
                'employee_no',
            ]);

            $table->index([
                'employer_id',
                'status',
            ]);

            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};