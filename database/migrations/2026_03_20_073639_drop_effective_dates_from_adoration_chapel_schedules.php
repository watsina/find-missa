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
        Schema::table('adoration_chapel_schedules', function (Blueprint $table) {
            $table->dropIndex(['effective_from', 'effective_until']);
            $table->dropColumn(['effective_from', 'effective_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adoration_chapel_schedules', function (Blueprint $table) {
            $table->date('effective_from')->nullable()->after('is_closed');
            $table->date('effective_until')->nullable()->after('effective_from');
            $table->index(['effective_from', 'effective_until']);
        });
    }
};
