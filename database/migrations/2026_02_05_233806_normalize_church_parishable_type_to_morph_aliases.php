<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Normalize parishable_type values to use morph map aliases.
     *
     * Some legacy records have full class names (App\Models\Parish)
     * instead of morph aliases (parish). This migration normalizes them
     * to ensure consistency and compatibility with the import/export system.
     */
    public function up(): void
    {
        DB::table('churches')
            ->where('parishable_type', 'App\\Models\\Parish')
            ->update(['parishable_type' => 'parish']);

        DB::table('churches')
            ->where('parishable_type', 'App\\Models\\SubParish')
            ->update(['parishable_type' => 'subParish']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal - morph aliases are the correct format
    }
};
