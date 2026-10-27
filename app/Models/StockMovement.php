<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'movement_type', // SALE, PURCHASE_IN, ADJUSTMENT_IN, ADJUSTMENT_OUT
        'qty_change',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'qty_change' => 'decimal:2',
            'stock_before' => 'decimal:2',
            'stock_after' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMovementLabelAttribute(): string
    {
        return match ($this->movement_type) {
            'SALE' => 'Penjualan Kasir',
            'PURCHASE_IN' => 'Barang Masuk',
            'ADJUSTMENT_IN' => 'Koreksi Tambah',
            'ADJUSTMENT_OUT' => 'Koreksi Kurang',
            default => $this->movement_type,
        };
    }
}
