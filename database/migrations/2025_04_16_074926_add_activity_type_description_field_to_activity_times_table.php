<?php

use Illuminate\Support\Facades\DB;
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
        Schema::table('activity_times', function (Blueprint $table) {
            $table->string('activity_type_description')->nullable()->after('type');
        });

        // Update existing records with values from another column
        DB::table('activity_times')->update([
            'activity_type_description' => DB::raw('type'), // or any other transformation
        ]);

        // Make the column non-nullable after populating
        Schema::table('activity_times', function (Blueprint $table) {
            $table->string('activity_type_description')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_times', function (Blueprint $table) {
            $table->dropColumn('activity_type_description');
        });
    }
};
