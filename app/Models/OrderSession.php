<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderSession extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'created_by',
        'title',
        'session_date',
        'closes_at',
        'status',
    ];

    protected $casts = [
        'session_date' => 'date',
        'closes_at'    => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
    public function isExpired(): bool
    {
        return $this->closes_at && $this->closes_at->isPast();
    }
}
