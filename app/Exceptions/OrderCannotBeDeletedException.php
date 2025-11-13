<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when an order cannot be deleted due to business rules.
 */
class OrderCannotBeDeletedException extends RuntimeException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $reason)
    {
        parent::__construct("Order cannot be deleted: {$reason}");
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
