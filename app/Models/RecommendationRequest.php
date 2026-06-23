<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecommendationRequest extends Model
{
    protected $fillable = [
        'agent_id',
        'status',
        'case_id',
        'error_message',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }
}
