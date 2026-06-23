<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'active',
        'policy_holder_id',
        'created_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean',
    ];

    // role checking

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isActive(): bool
    {
        return $this->active === true || $this->active === 1;
    }

    // permissions

    public function canCreateAgents(): bool
    {
        return $this->isAdmin();
    }

    public function canCreateClients(): bool
    {
        return $this->isAdmin() || $this->isAgent();
    }

    public function canManageProducts(): bool
    {
        return $this->isAdmin();
    }

    public function canManageWeights(): bool
    {
        return $this->isAdmin();
    }

    public function canUseCBR(): bool
    {
        return $this->isAdmin() || $this->isAgent();
    }

    public function canTrainModel(): bool
    {
        return $this->isAdmin();
    }

    // relationships

    /**
     * Users created by this user
     */
    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * User who created this user
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Consultations conducted by this user (as agent)
     */
    public function consultations()
    {
        return $this->hasMany(CaseModel::class, 'agent_id');
    }

    /**
     * Get agents created by this admin
     */
    public function agents()
    {
        return $this->hasMany(User::class, 'created_by')->where('role', 'agent');
    }

    /**
     * Get clients created by this user
     */
    public function clients()
    {
        return $this->hasMany(User::class, 'created_by')->where('role', 'client');
    }

    public function policyHolder()
    {
        return $this->belongsTo(PolicyHolder::class, 'policy_holder_id');
    }
}
