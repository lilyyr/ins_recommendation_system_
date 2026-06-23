<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlgorithmTestResult extends Model
{
    protected $table = 'algorithm_test_results';
    protected $fillable = [
        'algorithm_name',
        'split_ratio',
        'train_size',
        'test_size',
        'total_test_cases',
        'n_estimators',
        'max_depth',
        'max_features',
        'min_samples_leaf',
        'true_positives',
        'true_negatives',
        'false_negatives',
        'f1_score',
        'mrr',
        'hr_at_3',
        'hr_at_5',
        'mean_rank',
        'avg_time_taken',
        'total_time_taken',
        'detailed_results'
    ];

    //
}
