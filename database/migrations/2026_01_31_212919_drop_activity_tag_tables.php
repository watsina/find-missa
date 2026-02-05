<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drop all activity tag related tables. Tags are no longer needed in v2
     * because day_pattern field captures day types and time_of_day can be
     * computed from start_time at query time.
     */
    public function up(): void
    {
        // Drop pivot tables first (they have foreign keys to activity_tags)
        Schema::dropIfExists('activity_time_tag');
        Schema::dropIfExists('activity_schedule_tag');
        Schema::dropIfExists('one_time_activity_tag');
        Schema::dropIfExists('liturgical_schedule_time_tag');

        // Drop main table last
        Schema::dropIfExists('activity_tags');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal - tags are being permanently removed
    }
};
