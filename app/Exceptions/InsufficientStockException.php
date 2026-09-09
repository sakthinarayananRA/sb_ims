<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InsufficientStockException extends Exception
{
    /**
     * @param list<array{
     *     product_id: int,
     *     product_name: string,
     *     requested_quantity: int,
     *     stock_on_hand: int,
     *     shortage: int
     * }> $deficits
     */
    public function __construct(
        protected array $deficits,
        string $message = 'Insufficient stock available for one or more requested items.',
        int $code = Response::HTTP_UNPROCESSABLE_ENTITY,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the itemized deficits.
     *
     * @return list<array{
     *     product_id: int,
     *     product_name: string,
     *     requested_quantity: int,
     *     stock_on_hand: int,
     *     shortage: int
     * }>
     */
    public function getDeficits(): array
    {
        return $this->deficits;
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $this->getMessage(),
            'errors' => [
                'stock' => $this->deficits,
            ],
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}

