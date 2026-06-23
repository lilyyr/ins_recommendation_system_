<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null');

            // Financial Goals & Insurance Needs
            $table->json('financial_goals');
            $table->integer('insurance_period');
            $table->integer('premium_payment_period'); 
            $table->boolean('overseas_plans')->default(false);
            $table->boolean('has_existing_health_insurance')->default(false);

            // Physical Measurements
            $table->decimal('height', 5, 2); // cm
            $table->decimal('weight', 5, 2); // kg
            $table->decimal('bmi', 5, 2)->nullable();

            // Health Conditions (Boolean flags)
            $table->boolean('weight_change_last_year')->default(false);
            $table->boolean('smoked_last_year')->default(false);
            $table->boolean('hospitalization_last_5_years')->default(false);
            $table->boolean('lab_tests_last_5_years')->default(false);
            $table->boolean('accident_poisoning_last_5_years')->default(false);
            $table->boolean('has_disability')->default(false);
            $table->boolean('has_serious_illness')->default(false);
            $table->boolean('receiving_treatment')->default(false);
            $table->boolean('family_medical_history')->default(false);
            $table->boolean('is_pregnant')->nullable(); // Nullable for males

            // Health Details
            $table->text('health_details')->nullable();
            $table->decimal('health_risk_score', 5, 2)->default(0); // 0-25 scale

            // CBR Results
            $table->json('feature_vector'); // 18D feature vector
            $table->decimal('euclidean_score', 8, 6)->nullable();
            $table->decimal('weighted_euclidean_score', 8, 6)->nullable();
            $table->decimal('random_forest_score', 8, 6)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
