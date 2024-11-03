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

        Schema::create('sub_parishes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_email', 150)->nullable();
            $table->string('office_address')->nullable();
            $table->string('website_url')->nullable();
            $table->foreignId('parish_id')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('team_id');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_parishes');
    }
};
