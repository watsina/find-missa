<?php

use Illuminate\Support\Facades\DB;
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
        DB::connection()->getDriverName() === 'pgsql'
            ? $this->changeColumnsForPostgres()
            : $this->changeColumnsDefault();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_times', function (Blueprint $table) {
            $table->string('start_time', 100)->nullable()->change();
            $table->string('end_time', 100)->nullable()->change();
        });
    }

    private function changeColumnsForPostgres(): void
    {
        DB::statement('ALTER TABLE activity_times ALTER COLUMN start_time TYPE time USING start_time::time');
        DB::statement('ALTER TABLE activity_times ALTER COLUMN start_time DROP NOT NULL');
        DB::statement('ALTER TABLE activity_times ALTER COLUMN end_time TYPE time USING end_time::time');
        DB::statement('ALTER TABLE activity_times ALTER COLUMN end_time DROP NOT NULL');
    }

    private function changeColumnsDefault(): void
    {
        Schema::table('activity_times', function (Blueprint $table) {
            $table->time('start_time')->nullable()->change();
            $table->time('end_time')->nullable()->change();
        });
    }
};
