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

        Schema::create('dioceses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('diocese');
            $table->string('rite', 150)->default('roman-latin');
            $table->string('contact_email', 150)->nullable();
            $table->string('website_url')->nullable();
            $table->string('office_address')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('country')->default('Uganda');
            $table->timestamps();

            $table->unique(['name', 'country']);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dioceses');
    }
};
