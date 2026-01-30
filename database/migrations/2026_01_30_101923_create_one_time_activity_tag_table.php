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
        Schema::create('one_time_activity_tag', function (Blueprint $table) {
            $table->foreignId('one_time_activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['one_time_activity_id', 'activity_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_time_activity_tag');
    }
};
