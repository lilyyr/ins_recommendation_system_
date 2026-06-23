<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Occupation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'risk_score'
    ];

    protected $casts = [
        'risk_score' => 'float'
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
