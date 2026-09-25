<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integration_api_keys', function (Blueprint $table) {
            $table->json('scopes')->nullable();
        });

        // Preserve existing write access. Receipt reads require an explicit grant.
        DB::table('integration_api_keys')->update([
            'scopes' => json_encode(['receipts.create', 'funding.confirm']),
        ]);
    }

    public function down(): void
    {
        Schema::table('integration_api_keys', function (Blueprint $table) {
            $table->dropColumn('scopes');
        });
    }
};
