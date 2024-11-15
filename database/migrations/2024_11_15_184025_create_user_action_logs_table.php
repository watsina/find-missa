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
        Schema::create('user_action_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 40)->unique();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('name');
            $table->string('entity_id');
            $table->string('entity_type');
            $table->timestamp('occurred_at')->nullable()->index();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->json('meta')->nullable();

            $table->foreignId('team_id')->nullable();

            $table->index(['entity_type', 'entity_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_action_logs');
    }
};
