<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_preferences', function (Blueprint $table) {
            $table->json('filters')->nullable()->after('column_order');
            $table->string('sort_column', 50)->nullable()->after('filters');
            $table->string('sort_direction', 4)->default('desc')->after('sort_column');
            $table->string('date_range_mode', 30)->nullable()->after('sort_direction');
        });
    }

    public function down(): void
    {
        Schema::table('report_preferences', function (Blueprint $table) {
            $table->dropColumn(['filters', 'sort_column', 'sort_direction', 'date_range_mode']);
        });
    }
};
