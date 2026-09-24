<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {public function up():void {Schema::create('audit_logs',function(Blueprint $t){$t->id();$t->uuid('uuid')->unique();$t->foreignId('merchant_id')->nullable()->constrained()->nullOnDelete();$t->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();$t->string('event_type',50);$t->string('event');$t->string('subject_type',100)->nullable();$t->string('subject_reference')->nullable();$t->text('description');$t->json('metadata')->nullable();$t->ipAddress('ip_address')->nullable();$t->string('user_agent',1000)->nullable();$t->timestamp('occurred_at');$t->timestamps();$t->index(['merchant_id','event_type','occurred_at']);});}public function down():void{Schema::dropIfExists('audit_logs');}};
