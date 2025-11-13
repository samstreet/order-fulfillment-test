<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\DataTransferObjects\CreateOrderDTO;
use App\DataTransferObjects\OrderFiltersDTO;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Exceptions\OrderCannotBeDeletedException;
use App\Exceptions\OrderNotFoundException;
use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Service contract for order business logic and operations.
 *
 * This interface defines the business layer for order management,
 * orchestrating workflows and enforcing business rules.
 */
interface OrderServiceInterface
{
    /**
     * Retrieve all orders with optional filtering and pagination.
     *
     * @param array<string, mixed> $filters Filtering and pagination parameters
     * @return Collection<int, Order>|LengthAwarePaginator<Order>
     */
    public function getAllOrders(array $filters = []): Collection|LengthAwarePaginator;

    /**
     * Retrieve a single order by its ID.
     *
     * @param int $id The order ID
     * @return Order The order
     * @throws OrderNotFoundException If the order is not found
     */
    public function getOrderById(int $id): Order;

    /**
     * Create a new order with items.
     *
     * @param array<string, mixed> $data Order creation data
     * @return Order The created order
     * @throws \Exception If order creation fails
     */
    public function createOrder(array $data): Order;

    /**
     * Update the status of an order.
     *
     * @param int $id The order ID
     * @param OrderStatus $status The new status
     * @return Order The updated order
     * @throws OrderNotFoundException If the order is not found
     * @throws InvalidOrderStatusTransitionException If the status transition is invalid
     */
    public function updateOrderStatus(int $id, OrderStatus $status): Order;

    /**
     * Delete an order.
     *
     * @param int $id The order ID
     * @return bool True if successful
     * @throws OrderNotFoundException If the order is not found
     * @throws OrderCannotBeDeletedException If the order cannot be deleted
     */
    public function deleteOrder(int $id): bool;
}
