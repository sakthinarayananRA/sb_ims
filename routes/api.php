<?php

use App\Http\Controllers\Api\V1\CustomerOrderController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Version 1)
|--------------------------------------------------------------------------
|
| 1. Create order: POST /api/v1/orders
| 2. Fetch customer order history: GET /api/v1/customers/orders?email=...
| 3. Fetch low stock products: GET /api/v1/products/low-stock
| 4. Fetch all products: GET /api/v1/products
|
*/

Route::prefix('v1')->as('api.v1.')->group(function () {
    // 1. Create order
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

    // 2. Fetch customer order history by customer email in query string
    Route::get('/customers/orders', [CustomerOrderController::class, 'index'])->name('customers.orders');

    // 3. Fetch products below low-stock threshold
    Route::get('/products/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');

    // 4. Fetch all available products for frontend dropdown selection
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
});
