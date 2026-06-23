<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyHolder extends Model
{
    protected $fillable = ['name', 'dob', 'gender', 'income_range'];

    protected $casts = ['dob' => 'date'];

    public function cases()
    {
        return $this->hasMany(CaseModel::class, 'policy_holder_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'policy_holder_id');
    }
}
