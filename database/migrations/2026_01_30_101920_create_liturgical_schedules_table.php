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
        Schema::create('liturgical_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('holiday_id')->constrained()->cascadeOnDelete();
            $table->year('year')->nullable();
            $table->boolean('replaces_regular_schedule')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['church_id', 'holiday_id', 'year'], 'liturgical_schedule_unique');
            $table->index('church_id');
            $table->index('holiday_id');
            $table->index('team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liturgical_schedules');
    }
};
