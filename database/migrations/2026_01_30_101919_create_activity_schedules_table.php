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
        Schema::create('activity_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50)->default('mass');
            $table->string('activity_type_description', 255)->nullable();
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->string('language', 100);
            $table->string('day_pattern', 50);
            $table->json('days_of_week')->nullable();
            $table->unsignedTinyInteger('week_of_month')->nullable();
            $table->string('day_of_week', 20)->nullable();
            $table->boolean('has_day_variations')->default(false);
            $table->foreignId('schedule_period_id')
                ->nullable()
                ->constrained('schedule_periods')
                ->nullOnDelete();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->timestamps();

            $table->index('church_id');
            $table->index('team_id');
            $table->index('type');
            $table->index('day_pattern');
            $table->index('start_time');
            $table->index(['effective_from', 'effective_until']);
            $table->index('schedule_period_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_schedules');
    }
};
