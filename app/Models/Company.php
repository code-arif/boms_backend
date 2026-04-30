<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model {
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'status', 'plan', 'settings'];

    protected $casts = ['settings' => 'array'];

    public function users(): HasMany {
        return $this->hasMany(User::class);
    }

    public function teams(): HasMany {
        return $this->hasMany(Team::class);
    }

    public function menuItems(): HasMany {
        return $this->hasMany(MenuItem::class);
    }

    public function orders(): HasMany {
        return $this->hasMany(Order::class);
    }
}