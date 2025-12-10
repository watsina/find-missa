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
        Schema::create('activity_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category'); // time_of_day, day_type, audience, custom
            $table->string('description')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_user_visible')->default(true);
            $table->foreignId('team_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['team_id', 'is_system']);
            $table->index(['category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_tags');
    }
};
