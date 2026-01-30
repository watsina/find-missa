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
        Schema::create('schedule_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('reason')->nullable();
            $table->string('scope_type', 20);
            $table->unsignedBigInteger('scope_id');
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('activity_type', 50)->nullable();
            $table->json('target_times')->nullable();
            $table->json('target_day_patterns')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('action', 20);
            $table->json('replacement_times')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['scope_type', 'scope_id'], 'schedule_overrides_scope_index');
            $table->index('team_id');
            $table->index(['start_date', 'end_date'], 'schedule_overrides_dates_index');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_overrides');
    }
};
