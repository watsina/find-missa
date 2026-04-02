<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Rename Ordinary Time schedule periods from seasonal naming (Winter, Summer/Fall)
     * to liturgically universal naming (I, II) that works globally regardless of hemisphere.
     */
    public function up(): void
    {
        // Rename "Ordinary Time (Winter) YYYY" → "Ordinary Time I YYYY"
        DB::table('schedule_periods')
            ->where('slug', 'like', 'ordinary-time-winter-%')
            ->get()
            ->each(function ($period) {
                $year = str_replace('ordinary-time-winter-', '', $period->slug);

                DB::table('schedule_periods')
                    ->where('id', $period->id)
                    ->update([
                        'name' => "Ordinary Time I {$year}",
                        'slug' => "ordinary-time-i-{$year}",
                    ]);
            });

        // Rename "Ordinary Time (Summer/Fall) YYYY" → "Ordinary Time II YYYY"
        DB::table('schedule_periods')
            ->where('slug', 'like', 'ordinary-time-summer-%')
            ->get()
            ->each(function ($period) {
                $year = str_replace('ordinary-time-summer-', '', $period->slug);

                DB::table('schedule_periods')
                    ->where('id', $period->id)
                    ->update([
                        'name' => "Ordinary Time II {$year}",
                        'slug' => "ordinary-time-ii-{$year}",
                    ]);
            });
    }

    /**
     * Reverse the migration back to seasonal naming.
     */
    public function down(): void
    {
        DB::table('schedule_periods')
            ->where('slug', 'like', 'ordinary-time-i-%')
            ->get()
            ->each(function ($period) {
                $year = str_replace('ordinary-time-i-', '', $period->slug);

                DB::table('schedule_periods')
                    ->where('id', $period->id)
                    ->update([
                        'name' => "Ordinary Time (Winter) {$year}",
                        'slug' => "ordinary-time-winter-{$year}",
                    ]);
            });

        DB::table('schedule_periods')
            ->where('slug', 'like', 'ordinary-time-ii-%')
            ->get()
            ->each(function ($period) {
                $year = str_replace('ordinary-time-ii-', '', $period->slug);

                DB::table('schedule_periods')
                    ->where('id', $period->id)
                    ->update([
                        'name' => "Ordinary Time (Summer/Fall) {$year}",
                        'slug' => "ordinary-time-summer-{$year}",
                    ]);
            });
    }
};
