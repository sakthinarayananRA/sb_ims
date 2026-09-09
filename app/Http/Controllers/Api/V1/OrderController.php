<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    /**
     * Create a new order with stock validation, tax computation, and inventory deduction.
     */
    public function store(CreateOrderRequest $request, OrderService $orderService): JsonResponse
    {
        $order = $orderService->createOrder($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Order created successfully.',
            'data' => new OrderResource($order),
        ], Response::HTTP_CREATED);
    }
}

