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
        Schema::table('cases', function (Blueprint $table) {
            $table->renameColumn('premium_budget', 'nominal_received');
            $table->renameColumn('overseas_plans', 'overseas_medical_plans');
            $table->json('coverage_regions')->nullable()->after('overseas_medical_plans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn('nominal_received');
            $table->dropColumn('overseas_medical_plans');
            $table->dropColumn('coverage_regions');
        });
    }
};
