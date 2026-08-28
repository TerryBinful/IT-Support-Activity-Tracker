<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::create('report_preferences',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('name');$t->json('columns');$t->json('column_order');$t->boolean('is_default')->default(false);$t->timestamps();$t->index(['user_id','is_default']);});}public function down():void{Schema::dropIfExists('report_preferences');}};
