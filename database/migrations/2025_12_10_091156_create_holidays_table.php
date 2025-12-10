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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Holiday type and computation
            $table->string('type'); // fixed, easter_relative, computed

            // For fixed holidays: month and day (e.g., Dec 25 for Christmas)
            $table->unsignedTinyInteger('fixed_month')->nullable();
            $table->unsignedTinyInteger('fixed_day')->nullable();

            // For Easter-relative holidays: days offset from Easter Sunday
            // Negative = before Easter (e.g., -46 for Ash Wednesday)
            // Positive = after Easter (e.g., 49 for Pentecost)
            $table->smallInteger('easter_offset_days')->nullable();

            // For computed holidays: rule definition (JSON)
            // e.g., {"nth": 4, "day": "thursday", "month": 11} for 4th Thursday of November
            $table->json('computation_rule')->nullable();

            // Categorization
            $table->boolean('is_holy_day_of_obligation')->default(false);
            $table->boolean('is_universal')->default(true); // Universal Catholic vs team-specific
            $table->boolean('is_moveable')->default(false); // True for Easter-based feasts

            // Color/liturgical info (optional)
            $table->string('liturgical_color')->nullable(); // white, red, green, violet, rose, black

            // Team scope: null = universal (all teams), set = team-specific
            $table->foreignId('team_id')->nullable()->constrained()->cascadeOnDelete();

            $table->timestamps();

            // Indexes
            $table->index('type');
            $table->index('is_holy_day_of_obligation');
            $table->index('is_universal');
            $table->index(['team_id', 'is_universal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
