<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::table('wallets',function(Blueprint $t){$t->unsignedBigInteger('held_minor_units')->default(0)->after('balance_minor_units');$t->decimal('held_major_units',18,2)->default(0)->after('balance_major_units');});
  Schema::create('fund_holds',function(Blueprint $t){$t->id();$t->uuid('uuid')->unique();$t->foreignId('merchant_id')->constrained()->cascadeOnDelete();$t->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();$t->foreignId('source_wallet_id')->constrained('wallets');$t->foreignId('destination_wallet_id')->constrained('wallets');$t->string('document_reference')->unique();$t->string('source_type',100);$t->string('description')->nullable();$t->unsignedBigInteger('amount_minor_units');$t->decimal('amount_major_units',18,2);$t->string('currency',3)->default('PHP');$t->unsignedTinyInteger('status')->default(1);$t->timestamp('scheduled_release_at')->nullable();$t->timestamp('released_at')->nullable();$t->timestamp('returned_at')->nullable();$t->timestamps();});
 }
 public function down():void {Schema::dropIfExists('fund_holds');Schema::table('wallets',function(Blueprint $t){$t->dropColumn(['held_minor_units','held_major_units']);});}
};
