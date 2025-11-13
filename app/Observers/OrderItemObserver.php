<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderItem;

class OrderItemObserver
{
    /**
     * Handle the OrderItem "created" event.
     */
    public function created(OrderItem $item): void
    {
        $order = $item->order;
        if ($order instanceof Order) {
            $this->updateParentOrder($order);
        }
    }

    /**
     * Handle the OrderItem "updated" event.
     */
    public function updated(OrderItem $item): void
    {
        $order = $item->order;
        if ($order instanceof Order) {
            $this->updateParentOrder($order);
        }
    }

    /**
     * Handle the OrderItem "deleted" event.
     */
    public function deleted(OrderItem $item): void
    {
        // After deletion, get order by ID since relationship may not work
        $order = Order::find($item->order_id);
        if ($order instanceof Order) {
            $this->updateParentOrder($order);
        }
    }

    /**
     * Update the parent order's totals.
     */
    private function updateParentOrder(Order $order): void
    {
        /** @phpstan-ignore-next-line */
        $totalAmount = (string) $order->items()->sum('subtotal');
        /** @phpstan-ignore-next-line */
        $itemsCount = $order->items()->count();

        // Only update if values have changed to avoid infinite loops
        if ($order->total_amount !== $totalAmount || $order->items_count !== $itemsCount) {
            $order->total_amount = $totalAmount;
            $order->items_count = $itemsCount;
            $order->saveQuietly();
        }
    }
}
