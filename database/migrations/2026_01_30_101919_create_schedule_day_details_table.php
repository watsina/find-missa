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
        Schema::create('schedule_day_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_schedule_id')->constrained()->cascadeOnDelete();
            $table->string('day_of_week', 20);
            $table->string('language', 100)->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('activity_type_description', 255)->nullable();
            $table->boolean('is_skipped')->default(false);
            $table->timestamps();

            $table->unique(['activity_schedule_id', 'day_of_week'], 'schedule_day_unique');
            $table->index('activity_schedule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_day_details');
    }
};
