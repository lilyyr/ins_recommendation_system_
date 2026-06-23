<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'gender',
        'marital_status',
        'dob',
        'occupation_id',
        'income_range',
        'num_dependents'
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function occupation()
    {
        return $this->belongsTo(Occupation::class);
    }

    public function cases()
    {
        return $this->hasMany(CaseModel::class);
    }

    public function getAgeAttribute()
    {
        return Carbon::parse($this->dob)->age;
    }

    /**
     * Relationship: Customer may have a user account
     */
    public function user()
    {
        return $this->hasOne(User::class, 'customer_id');
    }
}
