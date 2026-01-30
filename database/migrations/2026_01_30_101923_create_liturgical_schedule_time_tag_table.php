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
        Schema::create('liturgical_schedule_time_tag', function (Blueprint $table) {
            $table->foreignId('liturgical_schedule_time_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['liturgical_schedule_time_id', 'activity_tag_id'], 'liturgical_time_tag_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liturgical_schedule_time_tag');
    }
};
