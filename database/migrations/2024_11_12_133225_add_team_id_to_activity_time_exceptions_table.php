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
        Schema::table('activity_time_exceptions', function (Blueprint $table) {
            $table->foreignId('team_id')->after('activity_time_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_time_exceptions', function (Blueprint $table) {
            $table->dropColumn('team_id');
        });
    }
};
