<?php

namespace App\Actions;

use App\Models\Church;
use App\Models\Parish;
use App\Models\Priest;
use App\Models\Diocese;
use App\Models\SubParish;
use App\Models\ActivityTime;
use App\Models\ScheduleRule;
use App\Models\AdorationChapel;
use App\Models\OneTimeActivity;
use App\Models\ActivitySchedule;
use App\Models\ScheduleOverride;
use App\Models\LiturgicalSchedule;
use Illuminate\Support\Facades\DB;
use App\Models\ActivityTimeException;
use Illuminate\Support\Facades\Schema;
use App\Models\AdorationChapelSchedule;
use App\Models\AdorationChapelException;

class ClearTeamData
{
    /**
     * Clear all domain data by truncating tables in dependency order.
     *
     * Uses Schema::disableForeignKeyConstraints() for database-agnostic
     * foreign key handling (works with MySQL, SQLite, and PostgreSQL).
     */
    public function execute(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            // Pivot tables first
            DB::table('liturgical_schedule_times')->truncate();
            DB::table('schedule_day_details')->truncate();
            DB::table('adoration_chapel_schedule_details')->truncate();

            // Adoration chapel tables (children of churches)
            AdorationChapelException::truncate();
            AdorationChapelSchedule::truncate();
            AdorationChapel::truncate();

            // V2 Schedule tables (children of churches)
            ActivitySchedule::truncate();
            OneTimeActivity::truncate();
            ScheduleOverride::truncate();
            ScheduleRule::truncate();
            LiturgicalSchedule::truncate();

            // Legacy tables
            ActivityTimeException::truncate();
            ActivityTime::truncate();

            // Core tables (churches depend on parishes/subparishes)
            Church::truncate();
            SubParish::truncate();
            Parish::truncate();
            Diocese::truncate();
            Priest::truncate();
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
