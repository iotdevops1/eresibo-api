<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id(); $table->uuid('uuid')->unique();
            $table->foreignId('merchant_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('issued_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('source_wallet_id')->nullable()->constrained('wallets')->restrictOnDelete();
            $table->foreignId('destination_wallet_id')->nullable()->constrained('wallets')->restrictOnDelete();
            $table->foreignId('wallet_transaction_id')->nullable()->unique()->constrained('wallet_transactions')->restrictOnDelete();
            $table->date('pay_period_start'); $table->date('pay_period_end'); $table->date('pay_date');
            $table->string('note',255)->nullable(); $table->char('currency',3)->default('PHP');
            $table->unsignedBigInteger('gross_amount_minor_units'); $table->unsignedBigInteger('deduction_amount_minor_units')->default(0); $table->unsignedBigInteger('net_amount_minor_units');
            $table->decimal('gross_amount_major_units',20,2); $table->decimal('deduction_amount_major_units',20,2)->default(0); $table->decimal('net_amount_major_units',20,2);
            $table->unsignedTinyInteger('status')->default(1)->comment('1=PENDING_ACKNOWLEDGEMENT, 2=ACKNOWLEDGED');
            $table->timestamp('acknowledged_at')->nullable(); $table->timestamps();
            $table->index(['merchant_id','employee_id','pay_date']); $table->index(['employee_id','status']);
        });
        Schema::create('payslip_lines', function (Blueprint $table) {
            $table->id(); $table->uuid('uuid')->unique(); $table->foreignId('payslip_id')->constrained()->cascadeOnDelete();
            $table->string('line_type',10); $table->string('description',100); $table->unsignedBigInteger('amount_minor_units'); $table->decimal('amount_major_units',20,2); $table->unsignedInteger('sort_order'); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payslip_lines'); Schema::dropIfExists('payslips'); }
};
