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
        Schema::table('policy_holders', function (Blueprint $table) {
            $table->enum('income_range', [
                'below_50m',
                '50m_100m',
                '100m_300m',
                '300m_500m',
                '500m_1b',
                'above_1b'
            ])->default('below_50m')->after('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_holders', function (Blueprint $table) {
            $table->dropColumn('income_range');
        });
    }
};
