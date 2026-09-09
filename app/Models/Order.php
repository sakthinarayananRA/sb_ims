<?php

namespace App\Models;

use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([OrderObserver::class])]
class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'customer_id',
        'status',
        'subtotal',
        'tax_amount',
        'grand_total',
        'paid_amount',
        'change_amount',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the customer who placed this order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all line items for this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get all products included in this order.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot(['quantity', 'unit_price', 'tax_percentage', 'subtotal', 'tax_amount', 'total'])
            ->withTimestamps();
    }

    /**
     * Get stock audit trail movements recorded for this order.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Recalculate and update subtotal, tax_amount, and grand_total based on line items.
     */
    public function recalculateTotals(): void
    {
        $this->loadMissing('items');

        $subtotal = $this->items->sum('subtotal');
        $taxAmount = $this->items->sum('tax_amount');
        $grandTotal = $subtotal + $taxAmount;

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->grand_total = $grandTotal;

        if ($this->paid_amount !== null && $this->paid_amount >= $grandTotal) {
            $this->change_amount = $this->paid_amount - $grandTotal;
        }

        $this->save();
    }

    /**
     * Calculate Indian Rupee currency denomination breakdown for the return change.
     *
     * @return array{summary: string, breakdown: array<int, int>}
     */
    public function calculateChangeDenominations(): array
    {
        $change = (float) ($this->change_amount ?? 0);
        if ($change <= 0) {
            return ['summary' => 'None', 'breakdown' => []];
        }

        $denominations = [500, 200, 100, 50, 20, 10, 5, 2, 1];
        $remaining = (int) floor($change);
        $breakdown = [];
        $parts = [];

        foreach ($denominations as $denom) {
            if ($remaining >= $denom) {
                $count = intdiv($remaining, $denom);
                $remaining %= $denom;
                $breakdown[$denom] = $count;
                $parts[] = "{$count}x{$denom}";
            }
        }

        return [
            'summary' => implode(' + ', $parts),
            'breakdown' => $breakdown,
        ];
    }
}

