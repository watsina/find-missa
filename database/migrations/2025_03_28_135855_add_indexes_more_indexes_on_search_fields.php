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
            $table->index('team_id');
            $table->index('activity_time_id');
        });
        Schema::table('activity_times', function (Blueprint $table) {
            $table->index('team_id');
            $table->index('church_id');
        });
        Schema::table('churches', function (Blueprint $table) {
            $table->index('team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_time_exceptions', function (Blueprint $table) {
            $table->dropIndex(['team_id']);
            $table->dropIndex(['activity_time_id']);
        });
        Schema::table('activity_times', function (Blueprint $table) {
            $table->dropIndex(['team_id']);
            $table->dropIndex(['church_id']);
        });
        Schema::table('churches', function (Blueprint $table) {
            $table->dropIndex(['team_id']);
        });
    }
};
