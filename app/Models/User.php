<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    public function isGudang(): bool
    {
        return $this->role === 'gudang';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function roleRedirectRoute(): string
    {
        return match ($this->role) {
            'kasir' => route('cashier.pos'),
            'gudang' => route('warehouse.dashboard'),
            'admin' => route('admin.dashboard'),
            default => route('login'),
        };
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }
}
