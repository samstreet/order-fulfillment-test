<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\Enums\OrderStatus;

/**
 * Data Transfer Object for order filtering and pagination.
 *
 * This immutable DTO encapsulates all filtering, searching, and pagination
 * parameters for querying orders, providing type-safe access to query parameters.
 */
final readonly class OrderFiltersDTO
{
    /**
     * @param OrderStatus|null $status Filter orders by status
     * @param string|null $search Search term for customer name, email, or order number
     * @param int|null $page Current page number for pagination
     * @param int|null $perPage Number of items per page
     */
    public function __construct(
        public ?OrderStatus $status = null,
        public ?string $search = null,
        public ?int $page = null,
        public ?int $perPage = null,
    ) {
    }

    /**
     * Create a DTO instance from an associative array.
     *
     * @param array<string, mixed> $filters
     * @return self
     */
    public static function fromArray(array $filters): self
    {
        return new self(
            status: isset($filters['status']) && is_string($filters['status'])
                ? OrderStatus::from($filters['status'])
                : null,
            search: isset($filters['search']) && is_string($filters['search'])
                ? $filters['search']
                : null,
            page: isset($filters['page']) && is_numeric($filters['page'])
                ? (int) $filters['page']
                : null,
            perPage: isset($filters['per_page']) && is_numeric($filters['per_page'])
                ? (int) $filters['per_page']
                : null,
        );
    }

    /**
     * Check if pagination is requested.
     */
    public function isPaginated(): bool
    {
        return $this->page !== null;
    }

    /**
     * Get the number of items per page, with a default value.
     */
    public function getPerPage(int $default = 15): int
    {
        return $this->perPage ?? $default;
    }
}
