<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Services\OrderServiceInterface;
use App\DataTransferObjects\CreateOrderDTO;
use App\DataTransferObjects\OrderFiltersDTO;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Exceptions\OrderCannotBeDeletedException;
use App\Exceptions\OrderNotFoundException;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service class for managing orders and business logic.
 *
 * This service implements the business layer for order operations,
 * orchestrating workflows between the repository and domain logic.
 * Uses dependency injection with repository pattern for better testability.
 */
class OrderService implements OrderServiceInterface
{
    /**
     * @param OrderRepositoryInterface $orderRepository Repository for order data access
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }
    /**
     * Get all orders with optional filtering, searching, and pagination.
     *
     * @param array<string, mixed> $filters Filtering and pagination parameters
     * @return Collection<int, Order>|LengthAwarePaginator<Order>
     */
    public function getAllOrders(array $filters = []): Collection|LengthAwarePaginator
    {
        $filtersDTO = OrderFiltersDTO::fromArray($filters);
        return $this->orderRepository->getAll($filtersDTO);
    }

    /**
     * Get a single order by ID.
     *
     * @param int $id The order ID
     * @return Order The order
     * @throws OrderNotFoundException If the order is not found
     */
    public function getOrderById(int $id): Order
    {
        $order = $this->orderRepository->findById($id);

        if (!$order instanceof Order) {
            throw new OrderNotFoundException($id);
        }

        return $order;
    }

    /**
     * Create a new order with items.
     *
     * @param array<string, mixed> $data Order creation data
     * @return Order The created order
     * @throws \Exception If order creation fails
     */
    public function createOrder(array $data): Order
    {
        $dataDTO = CreateOrderDTO::fromArray($data);

        return DB::transaction(function () use ($dataDTO): Order {
            // Prepare order attributes
            $orderAttributes = [
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => $dataDTO->customerName,
                'customer_email' => $dataDTO->customerEmail,
                'notes' => $dataDTO->notes,
                'status' => OrderStatus::PENDING,
                'ordered_at' => now(),
                'total_amount' => 0,
                'items_count' => 0,
            ];

            // Create the order
            $order = $this->orderRepository->create($orderAttributes);

            // Create order items if provided
            if (count($dataDTO->items) > 0) {
                foreach ($dataDTO->items as $itemDTO) {
                    $itemAttributes = [
                        'order_id' => $order->id,
                        'product_name' => $itemDTO->productName,
                        'quantity' => $itemDTO->quantity,
                        'unit_price' => $itemDTO->unitPrice,
                        'subtotal' => $itemDTO->calculateSubtotal(),
                    ];

                    OrderItem::create($itemAttributes);
                }
            }

            // Reload order with relationships and recalculated aggregates
            $order->load('items');
            $order->refresh();

            // Ensure totals are calculated properly
            $order->saveQuietly();

            return $order;
        });
    }

    /**
     * Update the status of an order.
     *
     * @param int $id The order ID
     * @param OrderStatus $status The new status
     * @return Order The updated order
     * @throws OrderNotFoundException If the order is not found
     * @throws InvalidOrderStatusTransitionException If the status transition is invalid
     */
    public function updateOrderStatus(int $id, OrderStatus $status): Order
    {
        return DB::transaction(function () use ($id, $status): Order {
            $order = $this->orderRepository->findById($id);

            if (!$order instanceof Order) {
                throw new OrderNotFoundException($id);
            }

            // Store original status for comparison
            $originalStatus = $order->status;

            // Validate status transition
            $this->validateStatusTransition($originalStatus, $status);

            // Update status
            $order->status = $status;

            // Set fulfilled_at timestamp when status changes to FULFILLED
            if ($status === OrderStatus::FULFILLED) {
                $order->fulfilled_at = now();
            }

            // Clear fulfilled_at if status changes away from FULFILLED
            // CRITICAL FIX #3: Removed dead code - wasChanged() won't work before save()
            if ($originalStatus === OrderStatus::FULFILLED && $status !== OrderStatus::FULFILLED) {
                $order->fulfilled_at = null;
            }

            $this->orderRepository->update($order);

            return $order;
        });
    }

    /**
     * Delete an order by ID.
     *
     * @param int $id The order ID
     * @return bool True if successful
     * @throws OrderNotFoundException If the order is not found
     * @throws OrderCannotBeDeletedException If the order cannot be deleted
     */
    public function deleteOrder(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $order = $this->orderRepository->findById($id);

            if (!$order instanceof Order) {
                throw new OrderNotFoundException($id);
            }

            // Validate that order can be deleted
            $this->validateOrderCanBeDeleted($order);

            return $this->orderRepository->delete($order);
        });
    }

    /**
     * Generate a unique order number in format ORD-XXXXXX.
     *
     * CRITICAL FIX #2: Uses database sequence to guarantee uniqueness
     * without race conditions. The auto-increment ID ensures no two
     * processes can get the same number, even under high concurrency.
     */
    private function generateOrderNumber(): string
    {
        // Use database auto-increment to guarantee uniqueness (fixes race condition)
        $sequence = DB::table('order_sequences')->insertGetId([
            'created_at' => now(),
        ]);

        return sprintf('ORD-%06d', $sequence);
    }

    /**
     * Validate if a status transition is allowed.
     *
     * @throws InvalidOrderStatusTransitionException
     */
    private function validateStatusTransition(OrderStatus $from, OrderStatus $to): void
    {
        // Define valid transitions
        $validTransitions = match ($from) {
            OrderStatus::PENDING => [OrderStatus::PROCESSING, OrderStatus::CANCELLED],
            OrderStatus::PROCESSING => [OrderStatus::FULFILLED, OrderStatus::CANCELLED],
            OrderStatus::FULFILLED => [],
            OrderStatus::CANCELLED => [],
        };

        if (!in_array($to, $validTransitions, true)) {
            throw new InvalidOrderStatusTransitionException($from, $to);
        }
    }

    /**
     * Validate if an order can be deleted.
     *
     * @throws OrderCannotBeDeletedException
     */
    private function validateOrderCanBeDeleted(Order $order): void
    {
        $reason = match ($order->status) {
            OrderStatus::PROCESSING => 'Order is currently being processed',
            OrderStatus::FULFILLED => 'Order has been fulfilled',
            default => null,
        };

        if ($reason !== null) {
            throw new OrderCannotBeDeletedException($reason);
        }
    }
}
