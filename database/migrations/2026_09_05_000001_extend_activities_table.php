<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->timestamp('follow_up_due_at')->nullable()->after('follow_up_action');
            $table->string('follow_up_status', 20)->nullable()->after('follow_up_due_at');
            $table->timestamp('follow_up_completed_at')->nullable()->after('follow_up_status');
            $table->index(['user_id', 'follow_up_due_at']);
            $table->index(['user_id', 'follow_up_status']);
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'follow_up_due_at']);
            $table->dropIndex(['user_id', 'follow_up_status']);
            $table->dropColumn(['follow_up_due_at', 'follow_up_status', 'follow_up_completed_at']);
        });
    }
};
