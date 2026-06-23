<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'categories',
        'base_premium',
        'active'
    ];

    protected $casts = [
        'categories' => 'array',
        'base_premium' => 'decimal:2',
        'active' => 'boolean'
    ];

    // Relationship
    public function cases()
    {
        return $this->hasMany(CaseModel::class);
    }
}
