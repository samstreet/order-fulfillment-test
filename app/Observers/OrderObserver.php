<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        $this->updateOrderTotals($order);
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Only recalculate if items relationship is loaded
        // to avoid unnecessary queries
        if ($order->relationLoaded('items')) {
            $this->updateOrderTotals($order);
        }
    }

    /**
     * Update order totals based on related items.
     */
    private function updateOrderTotals(Order $order): void
    {
        /** @phpstan-ignore-next-line */
        $totalAmount = (float) $order->items()->sum('subtotal');
        /** @phpstan-ignore-next-line */
        $itemsCount = $order->items()->count();

        // Only update if values have changed to avoid infinite loops
        if ((float) $order->total_amount !== $totalAmount || $order->items_count !== $itemsCount) {
            $order->total_amount = $totalAmount;
            $order->items_count = $itemsCount;
            $order->saveQuietly();
        }
    }
}
