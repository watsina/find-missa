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
        Schema::table('adoration_chapel_exceptions', function (Blueprint $table) {
            $table->string('day_pattern', 50)->after('reason');
            $table->json('days_of_week')->nullable()->after('day_pattern');
            $table->time('opens_at')->nullable()->after('days_of_week');
            $table->time('closes_at')->nullable()->after('opens_at');
            $table->boolean('is_open_24_hours')->default(false)->after('closes_at');
            $table->boolean('is_closed')->default(false)->after('is_open_24_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adoration_chapel_exceptions', function (Blueprint $table) {
            $table->dropColumn([
                'day_pattern',
                'days_of_week',
                'opens_at',
                'closes_at',
                'is_open_24_hours',
                'is_closed',
            ]);
        });
    }
};
