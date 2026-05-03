<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'user_id',
        'order_session_id',
        'total',
        'status',
        'notes',
    ];

    protected $casts = ['total' => 'decimal:2'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function session(): BelongsTo
    {
        return $this->belongsTo(OrderSession::class, 'order_session_id');
    }
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recalculateTotal(): void
    {
        $this->update(['total' => $this->items()->sum('subtotal')]);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
