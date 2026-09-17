<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (! Schema::hasColumn('payslips','receipt_id')) {
            Schema::table('payslips', function (Blueprint $table) {
                $table->foreignId('receipt_id')->nullable()->unique()->constrained('receipts')->restrictOnDelete();
            });
        }
    }
    public function down(): void {
        if (Schema::hasColumn('payslips','receipt_id')) {
            Schema::table('payslips', function (Blueprint $table) { $table->dropForeign(['receipt_id']); $table->dropColumn('receipt_id'); });
        }
    }
};
