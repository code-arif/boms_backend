<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes, HasCompanyScope;

    protected $fillable = ['company_id', 'name', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active'         => 'boolean',
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }
    public function isCompanyAdmin(): bool
    {
        return $this->role === 'company_admin';
    }
    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
