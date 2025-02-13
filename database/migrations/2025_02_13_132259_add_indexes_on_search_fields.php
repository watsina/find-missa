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
            $table->index('type');
            $table->index('start_time');
            $table->index('occurrence');
            $table->index('day_of_week');
            $table->index('date');
        });
        Schema::table('churches', function (Blueprint $table) {
            $table->index('name');
            $table->index('district');
            $table->index('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_times', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['start_time']);
            $table->dropIndex(['occurrence']);
            $table->dropIndex(['day_of_week']);
            $table->dropIndex(['date']);
        });
        Schema::table('churches', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['district']);
            $table->dropIndex(['city']);
        });
    }
};
