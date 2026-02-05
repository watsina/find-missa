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
        Schema::create('adoration_chapel_schedule_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adoration_chapel_schedule_id')
                ->constrained('adoration_chapel_schedules', 'id', 'chapel_schedule_fk')
                ->cascadeOnDelete();
            $table->string('day_of_week', 20);
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->boolean('is_open_24_hours')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->unique(['adoration_chapel_schedule_id', 'day_of_week'], 'chapel_schedule_day_unique');
            $table->index('adoration_chapel_schedule_id', 'chapel_schedule_detail_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adoration_chapel_schedule_details');
    }
};
