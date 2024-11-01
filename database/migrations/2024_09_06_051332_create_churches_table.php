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

        Schema::create('churches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 100)->default('parish');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('street_address')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('city')->nullable();
            $table->string('website_url')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->nullableMorphs('parishable');
            $table->foreignId('priest_id')->nullable()->constrained();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('churches');
    }
};
