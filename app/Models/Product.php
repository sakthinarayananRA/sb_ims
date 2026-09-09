<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'price_per_unit',
        'tax_percentage',
        'stock_on_hand',
        'low_stock_threshold',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_per_unit' => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'stock_on_hand' => 'integer',
            'low_stock_threshold' => 'integer',
        ];
    }

    /**
     * Get all order item lines for this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get all orders containing this product.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot(['quantity', 'unit_price', 'tax_percentage', 'subtotal', 'tax_amount', 'total'])
            ->withTimestamps();
    }

    /**
     * Get stock audit trail history for this product.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Scope a query to only include products with low stock.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_on_hand', '<=', 'low_stock_threshold');
    }

    /**
     * Determine if the product has low stock.
     */
    public function isLowStock(): bool
    {
        return $this->stock_on_hand <= $this->low_stock_threshold;
    }

    /**
     * Record a stock change and update stock_on_hand.
     */
    public function recordStockMovement(
        int $quantityChange,
        string $type,
        ?int $orderId = null,
        ?string $remarks = null
    ): StockMovement {
        $this->stock_on_hand += $quantityChange;
        $this->save();

        return $this->stockMovements()->create([
            'order_id' => $orderId,
            'type' => $type,
            'quantity_change' => $quantityChange,
            'stock_after' => $this->stock_on_hand,
            'remarks' => $remarks,
        ]);
    }
}
