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
        Schema::disableForeignKeyConstraints();

        Schema::create('activity_times', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('mass');
            $table->string('start_time', 100);
            $table->string('end_time', 100);
            $table->string('language', 100);
            $table->foreignId('church_id')->nullable()->constrained();
            $table->string('occurence', 100);
            $table->dateTime('date')->nullable();
            $table->string('day_of_week', 100)->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_times');
    }
};
