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
        Schema::create('activity_schedule_tag', function (Blueprint $table) {
            $table->foreignId('activity_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['activity_schedule_id', 'activity_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_schedule_tag');
    }
};
