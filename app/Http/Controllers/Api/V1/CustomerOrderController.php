<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerOrderFilterRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerOrderController extends Controller
{
    /**
     * Fetch order history for a customer using their email in query string, with filters.
     */
    public function index(CustomerOrderFilterRequest $request): JsonResponse
    {
        $email = strtolower(trim((string) $request->input('email')));

        $customer = Customer::where('email', $email)->first();
        if (!$customer) {
            throw new NotFoundHttpException("Customer with email '{$email}' does not exist in our records.");
        }

        $query = $customer->orders()->with(['items.product', 'customer']);

        // Filter by order status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        // Filter by amount range
        if ($request->filled('min_total')) {
            $query->where('grand_total', '>=', (float) $request->input('min_total'));
        }

        if ($request->filled('max_total')) {
            $query->where('grand_total', '<=', (float) $request->input('max_total'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = (int) $request->input('per_page', 15);
        $paginatedOrders = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Customer order history retrieved successfully.',
            'customer' => new CustomerResource($customer),
            'data' => OrderResource::collection($paginatedOrders->items()),
            'pagination' => [
                'total' => $paginatedOrders->total(),
                'count' => $paginatedOrders->count(),
                'per_page' => $paginatedOrders->perPage(),
                'current_page' => $paginatedOrders->currentPage(),
                'total_pages' => $paginatedOrders->lastPage(),
            ],
        ]);
    }
}

