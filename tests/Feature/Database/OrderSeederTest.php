<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\OrderStatus;
use App\Models\Order;
use Database\Seeders\OrderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_correct_number_of_orders(): void
    {
        $this->seed(OrderSeeder::class);

        $this->assertDatabaseCount('orders', 30);
    }

    public function test_seeder_creates_correct_pending_orders(): void
    {
        $this->seed(OrderSeeder::class);

        $pendingOrders = Order::where('status', OrderStatus::PENDING)->count();

        $this->assertEquals(7, $pendingOrders);
    }

    public function test_seeder_creates_correct_processing_orders(): void
    {
        $this->seed(OrderSeeder::class);

        $processingOrders = Order::where('status', OrderStatus::PROCESSING)->count();

        $this->assertEquals(8, $processingOrders);
    }

    public function test_seeder_creates_correct_fulfilled_orders(): void
    {
        $this->seed(OrderSeeder::class);

        $fulfilledOrders = Order::where('status', OrderStatus::FULFILLED)->count();

        $this->assertEquals(10, $fulfilledOrders);
    }

    public function test_seeder_creates_correct_cancelled_orders(): void
    {
        $this->seed(OrderSeeder::class);

        $cancelledOrders = Order::where('status', OrderStatus::CANCELLED)->count();

        $this->assertEquals(5, $cancelledOrders);
    }

    public function test_all_orders_have_items(): void
    {
        $this->seed(OrderSeeder::class);

        $orders = Order::all();

        foreach ($orders as $order) {
            $this->assertGreaterThan(0, $order->items()->count());
        }
    }

    public function test_order_items_count_is_between_expected_range(): void
    {
        $this->seed(OrderSeeder::class);

        $orders = Order::all();

        foreach ($orders as $order) {
            $itemCount = $order->items()->count();
            $this->assertGreaterThanOrEqual(1, $itemCount);
            $this->assertLessThanOrEqual(5, $itemCount);
        }
    }

    public function test_order_totals_match_item_sums(): void
    {
        $this->seed(OrderSeeder::class);

        $orders = Order::all();

        foreach ($orders as $order) {
            $expectedTotal = $order->items()->sum('subtotal');
            $expectedCount = $order->items()->count();

            $this->assertEquals(
                number_format((float) $expectedTotal, 2),
                number_format((float) $order->total_amount, 2),
                "Order {$order->id} total_amount mismatch"
            );

            $this->assertEquals(
                $expectedCount,
                $order->items_count,
                "Order {$order->id} items_count mismatch"
            );
        }
    }

    public function test_order_items_have_valid_calculations(): void
    {
        $this->seed(OrderSeeder::class);

        $orders = Order::all();

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $expectedSubtotal = round($item->quantity * (float) $item->unit_price, 2);
                $actualSubtotal = round((float) $item->subtotal, 2);

                $this->assertEquals(
                    $expectedSubtotal,
                    $actualSubtotal,
                    "OrderItem {$item->id} subtotal calculation incorrect"
                );
            }
        }
    }

    public function test_seeder_creates_orders_with_required_fields(): void
    {
        $this->seed(OrderSeeder::class);

        $orders = Order::all();

        foreach ($orders as $order) {
            $this->assertNotEmpty($order->order_number);
            $this->assertNotEmpty($order->customer_name);
            $this->assertNotEmpty($order->customer_email);
            $this->assertInstanceOf(OrderStatus::class, $order->status);
            $this->assertNotNull($order->total_amount);
            $this->assertNotNull($order->items_count);
            $this->assertNotNull($order->ordered_at);
        }
    }

    public function test_seeder_creates_order_items_with_required_fields(): void
    {
        $this->seed(OrderSeeder::class);

        $orders = Order::all();

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $this->assertNotNull($item->order_id);
                $this->assertNotEmpty($item->product_name);
                $this->assertGreaterThan(0, $item->quantity);
                $this->assertGreaterThan(0, (float) $item->unit_price);
                $this->assertGreaterThan(0, (float) $item->subtotal);
            }
        }
    }
}
