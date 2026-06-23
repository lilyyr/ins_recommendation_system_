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
        Schema::create('test_case', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('correct_product_id')->constrained('products'); // Ground truth
            $table->json('feature_vector');
            $table->text('notes')->nullable();
            $table->enum('validation_method', ['expert', 'historical', 'customer_feedback'])->default('expert');
            $table->timestamps();
        });

        Schema::create('algorithm_test_results', function (Blueprint $table) {
            $table->id();
            $table->string('algorithm_name'); // euclidean, weighted_euclidean, random_forest
            $table->timestamp('test_run_date');
            $table->integer('total_test_cases');

            // Confusion matrix
            $table->integer('true_positives');
            $table->integer('false_positives');
            $table->integer('true_negatives');
            $table->integer('false_negatives');

            // Calculated metrics
            $table->decimal('precision', 5, 4);
            $table->decimal('recall', 5, 4);
            $table->decimal('f1_score', 5, 4);
            $table->decimal('accuracy', 5, 4);
            $table->decimal('precision_at_5', 5, 4);
            $table->decimal('mrr', 5, 4);

            // Performance metrics
            $table->decimal('avg_execution_time_ms', 8, 2);
            $table->decimal('total_execution_time_ms', 10, 2);

            // Additional data
            $table->json('detailed_results')->nullable(); // Per-case results
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('algorithm_test_results');
        Schema::dropIfExists('test_case');
    }
};
