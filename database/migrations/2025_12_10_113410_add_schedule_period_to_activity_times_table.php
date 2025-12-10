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
            $table->foreignId('schedule_period_id')
                ->nullable()
                ->after('team_id')
                ->constrained()
                ->nullOnDelete();

            // Add effective dates for activities that aren't tied to a schedule period
            $table->date('effective_from')->nullable()->after('schedule_period_id');
            $table->date('effective_until')->nullable()->after('effective_from');

            $table->index(['schedule_period_id']);
            $table->index(['effective_from', 'effective_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_times', function (Blueprint $table) {
            $table->dropForeign(['schedule_period_id']);
            $table->dropColumn(['schedule_period_id', 'effective_from', 'effective_until']);
        });
    }
};
