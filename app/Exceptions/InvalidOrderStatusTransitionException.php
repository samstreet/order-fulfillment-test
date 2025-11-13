<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\OrderStatus;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when an invalid order status transition is attempted.
 */
class InvalidOrderStatusTransitionException extends RuntimeException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(OrderStatus $from, OrderStatus $to)
    {
        parent::__construct(
            "Cannot transition order from {$from->value} to {$to->value}"
        );
    }

    /**
     * Get the HTTP status code.
     */
    public function getStatusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
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
