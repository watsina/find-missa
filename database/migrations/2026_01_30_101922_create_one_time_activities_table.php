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
        Schema::create('one_time_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50)->default('mass');
            $table->string('activity_type_description', 255)->nullable();
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->string('language', 100);
            $table->date('date');
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->index('church_id');
            $table->index('team_id');
            $table->index('date');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_time_activities');
    }
};
