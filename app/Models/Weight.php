<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Weight extends Model
{
    protected $fillable = [
        'feature_name',
        'weight',
        'description'
    ];

    protected $casts = [
        'weight' => 'decimal:4'
    ];
}
