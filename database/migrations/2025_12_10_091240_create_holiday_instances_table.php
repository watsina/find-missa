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
        Schema::create('holiday_instances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('holiday_id')->constrained()->cascadeOnDelete();
            $table->year('year');
            $table->date('date');

            // Optional: override name for this specific instance
            // e.g., "Easter Sunday 2025" instead of just "Easter Sunday"
            $table->string('display_name')->nullable();

            // Optional: notes specific to this instance
            $table->text('notes')->nullable();

            $table->timestamps();

            // Each holiday can only have one instance per year
            $table->unique(['holiday_id', 'year']);

            // For querying holidays by date range
            $table->index('date');
            $table->index('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_instances');
    }
};
