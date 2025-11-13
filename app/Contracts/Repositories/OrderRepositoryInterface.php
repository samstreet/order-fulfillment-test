<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DataTransferObjects\OrderFiltersDTO;
use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Repository contract for managing order data persistence.
 *
 * This interface defines the data access layer for orders,
 * abstracting database operations and promoting testability.
 */
interface OrderRepositoryInterface
{
    /**
     * Retrieve all orders with optional filtering and pagination.
     *
     * @param OrderFiltersDTO $filters Filtering and pagination parameters
     * @return Collection<int, Order>|LengthAwarePaginator<Order>
     */
    public function getAll(OrderFiltersDTO $filters): Collection|LengthAwarePaginator;

    /**
     * Find an order by its ID.
     *
     * @param int $id The order ID
     * @return Order|null The order if found, null otherwise
     */
    public function findById(int $id): ?Order;

    /**
     * Create a new order.
     *
     * @param array<string, mixed> $attributes Order attributes
     * @return Order The created order
     */
    public function create(array $attributes): Order;

    /**
     * Update an existing order.
     *
     * @param Order $order The order to update
     * @return bool True if successful, false otherwise
     */
    public function update(Order $order): bool;

    /**
     * Delete an order.
     *
     * @param Order $order The order to delete
     * @return bool True if successful, false otherwise
     */
    public function delete(Order $order): bool;

    /**
     * Check if an order number already exists.
     *
     * @param string $orderNumber The order number to check
     * @return bool True if exists, false otherwise
     */
    public function existsByOrderNumber(string $orderNumber): bool;
}
