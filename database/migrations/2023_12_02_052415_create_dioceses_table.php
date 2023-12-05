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
        Schema::create('dioceses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('diocese');
            $table->string('office_address')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('country')->default('Uganda');
            $table->json('meta')->nullable(); // this will have other info like website url, socials, e.t.c
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dioceses');
    }
};
