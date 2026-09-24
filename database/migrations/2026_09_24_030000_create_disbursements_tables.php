<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('disbursements', function (Blueprint $table) {
   $table->id(); $table->uuid('uuid')->unique(); $table->foreignId('merchant_id')->constrained()->cascadeOnDelete(); $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
   $table->string('program_type',100); $table->string('program_name'); $table->text('note')->nullable(); $table->string('release_policy',40); $table->unsignedTinyInteger('status')->default(1); $table->string('currency',3)->default('PHP');
   $table->unsignedBigInteger('total_amount_minor_units')->default(0); $table->decimal('total_amount_major_units',18,2)->default(0); $table->timestamp('released_at')->nullable(); $table->timestamp('cancelled_at')->nullable(); $table->timestamps();
  });
  Schema::create('disbursement_beneficiaries', function (Blueprint $table) {
   $table->id(); $table->uuid('uuid')->unique(); $table->foreignId('disbursement_id')->constrained()->cascadeOnDelete(); $table->string('beneficiary_name'); $table->uuid('wallet_uuid')->nullable(); $table->string('email')->nullable(); $table->string('reference_no')->nullable(); $table->string('purpose')->nullable();
   $table->unsignedBigInteger('amount_minor_units'); $table->decimal('amount_major_units',18,2); $table->unsignedTinyInteger('status')->default(1); $table->foreignId('wallet_transaction_id')->nullable()->constrained('wallet_transactions')->nullOnDelete(); $table->timestamp('released_at')->nullable(); $table->timestamps();
  });
 }
 public function down(): void { Schema::dropIfExists('disbursement_beneficiaries'); Schema::dropIfExists('disbursements'); }
};
