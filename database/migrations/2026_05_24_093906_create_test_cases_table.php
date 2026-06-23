<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_case', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('correct_product_id')->constrained('products');

            // Customer demographics (same as customers table)
            $table->string('name');
            $table->enum('gender', ['male', 'female']);
            $table->date('dob');
            $table->enum('marital_status', ['single', 'married']);
            $table->foreignId('occupation_id')->constrained('occupations');
            $table->string('income_range');
            $table->integer('num_dependents')->default(0);

            // Physical measurements
            $table->decimal('height', 5, 2);
            $table->decimal('weight', 5, 2);

            // Insurance preferences
            $table->integer('insurance_period');
            $table->integer('premium_payment_period');
            $table->boolean('overseas_medical_plans')->default(false);
            // $table->json('coverage_regions')->nullable();
            $table->boolean('has_existing_health_insurance')->default(false);
            $table->boolean('high_risk_hobby')->default(false);

            // Financial
            $table->decimal('nominal_received', 15, 2);

            // Beneficiary
            $table->string('beneficiary_name');
            $table->enum('beneficiary_relationship', ['adik/kakak kandung', 'anak kandung', 'cucu/cicit', 'nenek/kakek kandung', 'orang tua kandung', 'suami/istri', 'lainnya']);

            // Financial goals
            $table->json('financial_goals');

            // Health questions
            $table->boolean('weight_change_last_year')->default(false);
            $table->boolean('smoked_last_year')->default(false);
            $table->boolean('hospitalization_last_5_years')->default(false);
            $table->boolean('lab_tests_last_5_years')->default(false);
            $table->boolean('accident_poisoning_last_5_years')->default(false);
            $table->boolean('has_disability')->default(false);
            $table->boolean('has_serious_illness')->default(false);
            $table->boolean('receiving_treatment')->default(false);
            $table->boolean('family_medical_history')->default(false);
            $table->boolean('is_pregnant')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_case');
    }
};
