<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'order_id',
        'order_session_id',
        'collected_by',
        'amount',
        'method',
        'status',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function session(): BelongsTo
    {
        return $this->belongsTo(OrderSession::class, 'order_session_id');
    }
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
