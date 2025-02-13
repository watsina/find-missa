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
        Schema::table('dioceses', function (Blueprint $table) {
            $table->string('code_name')->unique()->after('name');
        });
        Schema::table('parishes', function (Blueprint $table) {
            $table->string('code_name')->unique()->after('name');
        });
        Schema::table('sub_parishes', function (Blueprint $table) {
            $table->string('code_name')->unique()->after('name');
        });
        Schema::table('churches', function (Blueprint $table) {
            $table->string('code_name')->unique()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dioceses', function (Blueprint $table) {
            $table->dropColumn('code_name');
        });
        Schema::table('parishes', function (Blueprint $table) {
            $table->dropColumn('code_name');
        });
        Schema::table('sub_parishes', function (Blueprint $table) {
            $table->dropColumn('code_name');
        });
        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn('code_name');
        });
    }
};
