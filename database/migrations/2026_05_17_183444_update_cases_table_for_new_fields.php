<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            // Add new fields if they don't exist
            if (!Schema::hasColumn('cases', 'high_risk_hobby')) {
                $table->boolean('high_risk_hobby')->default(false)->after('has_existing_health_insurance');
            }
            if (!Schema::hasColumn('cases', 'premium_budget')) {
                $table->decimal('premium_budget', 15, 2)->nullable()->after('high_risk_hobby');
            }

            // Beneficiary information
            if (!Schema::hasColumn('cases', 'beneficiary_name')) {
                $table->string('beneficiary_name')->nullable()->after('premium_budget');
                $table->date('beneficiary_dob')->nullable()->after('beneficiary_name');
                $table->enum('beneficiary_gender', ['male', 'female'])->nullable()->after('beneficiary_dob');
                $table->enum('beneficiary_relationship', ['adik/kakak kandung', 'anak kandung', 'cucu/cicit', 'nenek/kakek kandung', 'orang tua kandung', 'suami/istri', 'lainnya'])->after('beneficiary_gender');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn([
                'high_risk_hobby',
                'premium_budget',
                'beneficiary_name',
                'beneficiary_dob',
                'beneficiary_gender',
                'beneficiary_relationship'
            ]);
        });
    }
};
