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
        Schema::create('global_activity_time_exceptions', function (Blueprint $table) {
            $table->id();
            $table->string('reason');
            $table->text('message')->nullable();
            $table->dateTime('start_date')->index();
            $table->dateTime('end_date')->index();
            $table->foreignId('team_id')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_activity_time_exceptions');
    }
};
