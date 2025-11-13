<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderTotalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_items_updates_order_totals(): void
    {
        $order = Order::factory()->create();

        $this->assertEquals('0.00', $order->total_amount);
        $this->assertEquals(0, $order->items_count);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 2,
            'unit_price' => 50.00,
            'subtotal' => 100.00,
        ]);

        $order->refresh();

        $this->assertEquals('100.00', $order->total_amount);
        $this->assertEquals(1, $order->items_count);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 3,
            'unit_price' => 25.00,
            'subtotal' => 75.00,
        ]);

        $order->refresh();

        $this->assertEquals('175.00', $order->total_amount);
        $this->assertEquals(2, $order->items_count);
    }

    public function test_deleting_items_updates_order_totals(): void
    {
        $order = Order::factory()->create();

        $item1 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'subtotal' => 100.00,
        ]);

        $item2 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'subtotal' => 50.00,
        ]);

        $order->refresh();

        $this->assertEquals('150.00', $order->total_amount);
        $this->assertEquals(2, $order->items_count);

        $item1->delete();

        $order->refresh();

        $this->assertEquals('50.00', $order->total_amount);
        $this->assertEquals(1, $order->items_count);

        $item2->delete();

        $order->refresh();

        $this->assertEquals('0.00', $order->total_amount);
        $this->assertEquals(0, $order->items_count);
    }

    public function test_updating_item_quantity_updates_order_totals(): void
    {
        $order = Order::factory()->create();

        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 2,
            'unit_price' => 50.00,
            'subtotal' => 100.00,
        ]);

        $order->refresh();

        $this->assertEquals('100.00', $order->total_amount);

        $item->update([
            'quantity' => 5,
            'subtotal' => 250.00,
        ]);

        $order->refresh();

        $this->assertEquals('250.00', $order->total_amount);
    }

    public function test_updating_item_unit_price_updates_order_totals(): void
    {
        $order = Order::factory()->create();

        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 2,
            'unit_price' => 50.00,
            'subtotal' => 100.00,
        ]);

        $order->refresh();

        $this->assertEquals('100.00', $order->total_amount);

        $item->update([
            'unit_price' => 75.00,
            'subtotal' => 150.00,
        ]);

        $order->refresh();

        $this->assertEquals('150.00', $order->total_amount);
    }

    public function test_updating_item_subtotal_directly_updates_order_totals(): void
    {
        $order = Order::factory()->create();

        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'subtotal' => 100.00,
        ]);

        $order->refresh();

        $this->assertEquals('100.00', $order->total_amount);

        $item->update(['subtotal' => 200.00]);

        $order->refresh();

        $this->assertEquals('200.00', $order->total_amount);
    }

    public function test_multiple_items_calculate_correct_totals(): void
    {
        $order = Order::factory()->create();

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'subtotal' => 25.50,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'subtotal' => 30.75,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'subtotal' => 44.25,
        ]);

        $order->refresh();

        $this->assertEquals('100.50', $order->total_amount);
        $this->assertEquals(3, $order->items_count);
    }

    public function test_order_totals_precision_is_maintained(): void
    {
        $order = Order::factory()->create();

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'subtotal' => 10.99,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'subtotal' => 20.01,
        ]);

        $order->refresh();

        $this->assertEquals('31.00', $order->total_amount);
    }

    public function test_deleting_all_items_resets_order_totals_to_zero(): void
    {
        $order = Order::factory()->create();

        $items = OrderItem::factory()->count(3)->create([
            'order_id' => $order->id,
        ]);

        $order->refresh();

        $this->assertGreaterThan(0, (float) $order->total_amount);
        $this->assertEquals(3, $order->items_count);

        foreach ($items as $item) {
            $item->delete();
        }

        $order->refresh();

        $this->assertEquals('0.00', $order->total_amount);
        $this->assertEquals(0, $order->items_count);
    }

    public function test_bulk_creating_items_updates_order_totals_correctly(): void
    {
        $order = Order::factory()->create();

        $items = collect([
            ['order_id' => $order->id, 'product_name' => 'Product 1', 'quantity' => 1, 'unit_price' => 10.00, 'subtotal' => 10.00],
            ['order_id' => $order->id, 'product_name' => 'Product 2', 'quantity' => 2, 'unit_price' => 20.00, 'subtotal' => 40.00],
            ['order_id' => $order->id, 'product_name' => 'Product 3', 'quantity' => 3, 'unit_price' => 30.00, 'subtotal' => 90.00],
        ]);

        foreach ($items as $itemData) {
            OrderItem::factory()->create($itemData);
        }

        $order->refresh();

        $this->assertEquals('140.00', $order->total_amount);
        $this->assertEquals(3, $order->items_count);
    }
}
