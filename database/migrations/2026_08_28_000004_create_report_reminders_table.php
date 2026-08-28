<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::create('report_reminders',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->date('report_month');$t->timestamp('reminded_at')->nullable();$t->timestamp('acknowledged_at')->nullable();$t->timestamps();$t->unique(['user_id','report_month']);});}public function down():void{Schema::dropIfExists('report_reminders');}};
