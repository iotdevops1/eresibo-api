<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_batch_items', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            /*
            |--------------------------------------------------------------------------
            | Payroll Batch
            |--------------------------------------------------------------------------
            */

            $table->foreignId('payroll_batch_id')
                ->constrained('payroll_batches')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Employee
            |--------------------------------------------------------------------------
            */

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Payroll Amounts
            |--------------------------------------------------------------------------
            */

            $table->decimal('gross_amount', 15, 2)
                ->default(0);

            $table->decimal('deduction_amount', 15, 2)
                ->default(0);

            $table->decimal('net_amount', 15, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Item Status
            |--------------------------------------------------------------------------
            */

            $table->unsignedTinyInteger('status')
                ->default(1)
                ->comment(
                    '1=PENDING, 2=PROCESSING, 3=COMPLETED, 4=FAILED, 5=CANCELLED'
                );

            /*
            |--------------------------------------------------------------------------
            | Payslip
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('payslip_id')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Payout
            |--------------------------------------------------------------------------
            */

            $table->string('payout_status', 30)
                ->nullable();

            $table->string('payout_reference', 100)
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Constraints / Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'payroll_batch_id',
                'employee_id',
            ]);

            $table->index([
                'payroll_batch_id',
                'status',
            ]);

            $table->index('employee_id');

            $table->index('payout_status');

            $table->index('payout_reference');

            $table->index('payslip_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_batch_items');
    }
};