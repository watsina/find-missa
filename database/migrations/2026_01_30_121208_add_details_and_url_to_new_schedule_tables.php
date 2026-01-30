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
        Schema::table('activity_schedules', function (Blueprint $table) {
            $table->text('details')->nullable()->after('effective_until');
            $table->string('url')->nullable()->after('details');
        });

        Schema::table('one_time_activities', function (Blueprint $table) {
            $table->text('details')->nullable()->after('date');
            $table->string('url')->nullable()->after('details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_schedules', function (Blueprint $table) {
            $table->dropColumn(['details', 'url']);
        });

        Schema::table('one_time_activities', function (Blueprint $table) {
            $table->dropColumn(['details', 'url']);
        });
    }
};
