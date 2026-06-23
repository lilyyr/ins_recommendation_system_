<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('test_case', function (Blueprint $table) {
            $table->json('coverage_regions')->nullable()->after('overseas_medical_plans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_case', function (Blueprint $table) {
            $table->dropColumn('coverage_regions');
        });
    }
};
