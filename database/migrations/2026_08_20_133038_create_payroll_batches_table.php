<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_batches', function (Blueprint $table) {

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
            | Batch Identification
            |--------------------------------------------------------------------------
            */

            $table->string('batch_no', 50);

            /*
            |--------------------------------------------------------------------------
            | Payroll Period
            |--------------------------------------------------------------------------
            */

            $table->date('pay_period_start');

            $table->date('pay_period_end');

            $table->date('pay_date');

            /*
            |--------------------------------------------------------------------------
            | Description
            |--------------------------------------------------------------------------
            */

            $table->string('description', 255)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Payroll Summary
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('total_employees')
                ->default(0);

            $table->decimal('total_gross_amount', 15, 2)
                ->default(0);

            $table->decimal('total_deduction_amount', 15, 2)
                ->default(0);

            $table->decimal('total_net_amount', 15, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Processing Status
            |--------------------------------------------------------------------------
            */

            $table->unsignedTinyInteger('status')
                ->default(1)
                ->comment(
                    '1=DRAFT, 2=PROCESSING, 3=SUBMITTED, 4=PARTIALLY_PROCESSED, 5=COMPLETED, 6=FAILED, 7=CANCELLED'
                );

            /*
            |--------------------------------------------------------------------------
            | Processing Dates
            |--------------------------------------------------------------------------
            */

            $table->timestamp('submitted_at')
                ->nullable();

            $table->timestamp('completed_at')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Constraints / Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'employer_id',
                'batch_no',
            ]);

            $table->index([
                'employer_id',
                'status',
            ]);

            $table->index([
                'pay_period_start',
                'pay_period_end',
            ]);

            $table->index('pay_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_batches');
    }
};