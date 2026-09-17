<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasColumn('payslips', 'source_wallet_id')) {
            Schema::table('payslips', function (Blueprint $table) {
                $table->foreignId('source_wallet_id')->nullable()->change();
                $table->foreignId('destination_wallet_id')->nullable()->change();
                $table->foreignId('wallet_transaction_id')->nullable()->change();
            });
        }

        if (Schema::hasColumn('payslips', 'issued_at')) {
            Schema::table('payslips', function (Blueprint $table) {
                $table->timestamp('issued_at')->nullable()->change();
            });
        }

        if (! Schema::hasColumn('payslips', 'acknowledged_at')) {
            Schema::table('payslips', function (Blueprint $table) {
                $table->timestamp('acknowledged_at')->nullable();
            });
        }
    }

    public function down(): void {
        if (Schema::hasColumn('payslips', 'acknowledged_at')) {
            Schema::table('payslips', function (Blueprint $table) {
                $table->dropColumn('acknowledged_at');
            });
        }
    }
};
