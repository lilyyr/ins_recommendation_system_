<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreeVisualizationRequest extends Model
{
    protected $fillable = [
        'case_id',
        'status',
        'num_trees',
        'trees',
        'error_message',
    ];

    protected $casts = [
        'trees' => 'array',
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }
}
