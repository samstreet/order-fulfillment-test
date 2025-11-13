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
        // PHP 8.4: Define filter mappings for cleaner code
        $filterMappings = [
            'status' => fn($value) => is_string($value) ? OrderStatus::from($value) : null,
            'search' => fn($value) => is_string($value) ? $value : null,
            'page' => fn($value) => is_numeric($value) ? (int) $value : null,
            'per_page' => fn($value) => is_numeric($value) ? (int) $value : null,
        ];

        // PHP 8.4: Use array_find_key to find the first invalid filter key
        $firstInvalidKey = array_find_key(
            $filters,
            fn($value, $key) => !array_key_exists($key, $filterMappings)
        );

        if ($firstInvalidKey !== null) {
            throw new \InvalidArgumentException("Invalid filter key: {$firstInvalidKey}");
        }

        return new self(
            status: isset($filters['status']) ? $filterMappings['status']($filters['status']) : null,
            search: isset($filters['search']) ? $filterMappings['search']($filters['search']) : null,
            page: isset($filters['page']) ? $filterMappings['page']($filters['page']) : null,
            perPage: isset($filters['per_page']) ? $filterMappings['per_page']($filters['per_page']) : null,
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
