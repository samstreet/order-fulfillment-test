<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 30 orders with different statuses
        // 7 pending orders
        Order::factory()
            ->count(7)
            ->pending()
            ->create()
            ->each(function (Order $order) {
                $this->createOrderItems($order);
            });

        // 8 processing orders
        Order::factory()
            ->count(8)
            ->processing()
            ->create()
            ->each(function (Order $order) {
                $this->createOrderItems($order);
            });

        // 10 fulfilled orders
        Order::factory()
            ->count(10)
            ->fulfilled()
            ->create()
            ->each(function (Order $order) {
                $this->createOrderItems($order);
            });

        // 5 cancelled orders
        Order::factory()
            ->count(5)
            ->cancelled()
            ->create()
            ->each(function (Order $order) {
                $this->createOrderItems($order);
            });
    }

    /**
     * Create random order items for an order.
     * Order totals are automatically calculated by observers.
     */
    private function createOrderItems(Order $order): void
    {
        $itemCount = fake()->numberBetween(1, 5);

        for ($i = 0; $i < $itemCount; $i++) {
            $quantity = fake()->numberBetween(1, 5);
            $unitPrice = fake()->randomFloat(2, 10, 500);
            $subtotal = round($quantity * $unitPrice, 2);

            OrderItem::factory()->create([
                'order_id' => $order->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ]);
        }
    }
}
