<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Product
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'price_per_unit' => (float) $this->price_per_unit,
            'tax_percentage' => (float) $this->tax_percentage,
            'stock_on_hand' => (int) $this->stock_on_hand,
            'low_stock_threshold' => (int) $this->low_stock_threshold,
            'is_low_stock' => $this->isLowStock(),
            'deficit' => max(0, $this->low_stock_threshold - $this->stock_on_hand),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

