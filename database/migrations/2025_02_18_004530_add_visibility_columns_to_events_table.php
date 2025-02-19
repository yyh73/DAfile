<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('show_in_month')->default(true); // 月カレンダーに表示
            $table->boolean('show_in_week')->default(true); // 週カレンダーに表示
            $table->boolean('show_in_day')->default(true); // 日カレンダーに表示
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['show_in_month', 'show_in_week', 'show_in_day']);
        });
    }
};
