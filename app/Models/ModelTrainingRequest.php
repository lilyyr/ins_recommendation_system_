<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelTrainingRequest extends Model
{
    protected $fillable = [
        'status',
        'output',
        'error_message'
    ];

    protected $casts = [
        'output' => 'array'
    ];
}
