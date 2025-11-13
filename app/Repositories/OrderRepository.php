<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DataTransferObjects\OrderFiltersDTO;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Repository implementation for managing order data persistence.
 *
 * This class handles all database operations for orders, implementing
 * proper SQL injection prevention and query optimization.
 */
class OrderRepository implements OrderRepositoryInterface
{
    /**
     * Retrieve all orders with optional filtering and pagination.
     *
     * @param OrderFiltersDTO $filters Filtering and pagination parameters
     * @return Collection<int, Order>|LengthAwarePaginator<Order>
     */
    public function getAll(OrderFiltersDTO $filters): Collection|LengthAwarePaginator
    {
        $query = Order::query()->with('items');

        // Apply status filter
        if ($filters->status !== null) {
            $query->byStatus($filters->status);
        }

        // Apply search filter with SQL injection prevention
        if ($filters->search !== null && trim($filters->search) !== '') {
            // CRITICAL FIX #1: Escape LIKE wildcards to prevent SQL injection
            // This prevents malicious input like "%' OR 1=1 --" from breaking the query
            $searchTerm = $this->escapeLikeWildcards(trim($filters->search));

            $query->where(function (Builder $q) use ($searchTerm): void {
                $q->where('order_number', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('customer_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('customer_email', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Sort by most recent first
        $query->orderBy('ordered_at', 'desc');

        // Apply pagination if requested
        if ($filters->isPaginated()) {
            $perPage = $filters->getPerPage();
            return $query->paginate($perPage, ['*'], 'page', $filters->page);
        }

        return $query->get();
    }

    /**
     * Find an order by its ID.
     *
     * @param int $id The order ID
     * @return Order|null The order if found, null otherwise
     */
    public function findById(int $id): ?Order
    {
        return Order::with('items')->find($id);
    }

    /**
     * Create a new order.
     *
     * @param array<string, mixed> $attributes Order attributes
     * @return Order The created order
     */
    public function create(array $attributes): Order
    {
        return Order::create($attributes);
    }

    /**
     * Update an existing order.
     *
     * @param Order $order The order to update
     * @return bool True if successful, false otherwise
     */
    public function update(Order $order): bool
    {
        return $order->save();
    }

    /**
     * Delete an order.
     *
     * @param Order $order The order to delete
     * @return bool True if successful, false otherwise
     */
    public function delete(Order $order): bool
    {
        return $order->delete() ?? false;
    }

    /**
     * Check if an order number already exists.
     *
     * @param string $orderNumber The order number to check
     * @return bool True if exists, false otherwise
     */
    public function existsByOrderNumber(string $orderNumber): bool
    {
        return Order::where('order_number', $orderNumber)->exists();
    }

    /**
     * Escape LIKE wildcard characters to prevent SQL injection.
     *
     * This method escapes backslashes, percent signs, and underscores
     * which are special characters in SQL LIKE clauses.
     *
     * @param string $value The value to escape
     * @return string The escaped value
     */
    private function escapeLikeWildcards(string $value): string
    {
        return str_replace(
            ['\\', '%', '_'],
            ['\\\\', '\\%', '\\_'],
            $value
        );
    }
}
