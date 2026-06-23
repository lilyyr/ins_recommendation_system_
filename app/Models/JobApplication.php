<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'date_of_birth',
        'address',
        'education_level',
        'institution',
        'work_experience',
        'insurance_experience',
        'motivation',
        'resume_path',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date'
    ];
}
