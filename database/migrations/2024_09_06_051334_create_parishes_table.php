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

        Schema::create('parishes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_email', 150)->nullable();
            $table->string('website_url')->nullable();
            $table->foreignId('diocese_id')->nullable()->constrained();
            $table->string('office_address')->nullable();
            $table->string('city')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parishes');
    }
};
