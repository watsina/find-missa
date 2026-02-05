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
        Schema::create('schedule_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('scope_type', 20);
            $table->unsignedBigInteger('scope_id');
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('activity_type', 50)->nullable();
            $table->json('target_times')->nullable();
            $table->json('target_day_patterns')->nullable();
            $table->string('condition_type', 50);
            $table->json('condition_holidays')->nullable();
            $table->string('action', 20);
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();

            $table->index(['scope_type', 'scope_id'], 'schedule_rules_scope_index');
            $table->index('team_id');
            $table->index('condition_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_rules');
    }
};
