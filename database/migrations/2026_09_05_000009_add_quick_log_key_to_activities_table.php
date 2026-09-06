<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->uuid('quick_log_key')->nullable()->after('evidence_url');
            $table->unique(['user_id', 'quick_log_key']);
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'quick_log_key']);
            $table->dropColumn('quick_log_key');
        });
    }
};