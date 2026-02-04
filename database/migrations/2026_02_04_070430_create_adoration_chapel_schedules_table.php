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
        Schema::create('adoration_chapel_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adoration_chapel_id')->constrained()->cascadeOnDelete();
            $table->string('day_pattern', 50);
            $table->json('days_of_week')->nullable();
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->boolean('is_open_24_hours')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->timestamps();

            $table->index('adoration_chapel_id');
            $table->index(['effective_from', 'effective_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adoration_chapel_schedules');
    }
};
