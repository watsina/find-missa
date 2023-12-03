<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sub_parishes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('parish_id');
            $table->string('district')->nullable();
            $table->string('office_address')->nullable();
            $table->json('meta')->nullable(); // any other info we might need later
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_parishes');
    }
};
