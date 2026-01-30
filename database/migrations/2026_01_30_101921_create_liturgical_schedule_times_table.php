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
        Schema::create('liturgical_schedule_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('liturgical_schedule_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50)->default('mass');
            $table->string('activity_type_description', 255)->nullable();
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->string('language', 100);
            $table->timestamps();

            $table->index('liturgical_schedule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liturgical_schedule_times');
    }
};
