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
        Schema::create('parishes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('diocese_id');
            $table->string('office_address')->nullable();
            $table->string('district')->nullable(); // this will hold the state for the US and other countries that don't have districts
            $table->json('meta')->nullable(); // for website, socials and other info we might later need to add
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parishes');
    }
};
