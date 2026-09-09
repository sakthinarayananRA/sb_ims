<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LowStockFilterRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Fetch all available products for order creation dropdowns.
     */
    public function index(): JsonResponse
    {
        $products = Product::orderBy('name')->get();

        return response()->json([
            'status' => true,
            'message' => 'Products retrieved successfully.',
            'data' => ProductResource::collection($products),
        ]);
    }

    /**
     * Fetch products below a configurable low-stock threshold.
     */
    public function lowStock(LowStockFilterRequest $request): JsonResponse
    {
        $hasCustomThreshold = $request->has('threshold');
        $threshold = $hasCustomThreshold
            ? (int) $request->input('threshold')
            : (int) config('inventory.default_low_stock_threshold', 10);

        $query = Product::query();

        if ($hasCustomThreshold) {
            $query->where('stock_on_hand', '<=', $threshold);
        } else {
            // Evaluates each product against its configured threshold
            $query->lowStock();
        }

        $perPage = (int) $request->input('per_page', 15);
        $paginatedProducts = $query->orderBy('stock_on_hand', 'asc')->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Low stock inventory products retrieved successfully.',
            'applied_threshold' => $hasCustomThreshold ? $threshold : 'per_product_threshold',
            'data' => ProductResource::collection($paginatedProducts->items()),
            'pagination' => [
                'total' => $paginatedProducts->total(),
                'count' => $paginatedProducts->count(),
                'per_page' => $paginatedProducts->perPage(),
                'current_page' => $paginatedProducts->currentPage(),
                'total_pages' => $paginatedProducts->lastPage(),
            ],
        ]);
    }
}
