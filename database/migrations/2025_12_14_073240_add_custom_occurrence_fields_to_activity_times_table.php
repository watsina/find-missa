<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activity_times', function (Blueprint $table) {
            // For monthly-nth-weekday: which week of the month (1=first, 2=second, ..., 5=last)
            $table->tinyInteger('week_of_month')->nullable()->after('day_of_week');

            // For bi-weekly: the start date to calculate alternating weeks from
            $table->date('bi_weekly_start_date')->nullable()->after('week_of_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_times', function (Blueprint $table) {
            $table->dropColumn(['week_of_month', 'bi_weekly_start_date']);
        });
    }
};
