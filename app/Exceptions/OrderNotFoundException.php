<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when an order cannot be found.
 *
 * @extends ModelNotFoundException<\App\Models\Order>
 */
class OrderNotFoundException extends ModelNotFoundException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(int $orderId)
    {
        parent::__construct("Order with ID {$orderId} not found");
    }

    /**
     * Get the HTTP status code.
     */
    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->getStatusCode());
    }
}
