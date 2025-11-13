<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class OrderIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_order_with_items_in_transaction(): void
    {
        DB::beginTransaction();

        try {
            $order = Order::factory()->create([
                'order_number' => 'TEST-001',
                'customer_name' => 'Test Customer',
                'status' => OrderStatus::PENDING,
            ]);

            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_name' => 'Product 1',
                'quantity' => 2,
                'unit_price' => 50.00,
                'subtotal' => 100.00,
            ]);

            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_name' => 'Product 2',
                'quantity' => 1,
                'unit_price' => 25.00,
                'subtotal' => 25.00,
            ]);

            DB::commit();

            $order->refresh();

            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'order_number' => 'TEST-001',
            ]);

            $this->assertDatabaseCount('order_items', 2);
            $this->assertEquals('125.00', $order->total_amount);
            $this->assertEquals(2, $order->items_count);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function test_transaction_rollback_prevents_order_and_items_creation(): void
    {
        $initialOrderCount = Order::count();
        $initialItemCount = OrderItem::count();

        DB::beginTransaction();

        try {
            $order = Order::factory()->create([
                'order_number' => 'ROLLBACK-001',
            ]);

            OrderItem::factory()->create([
                'order_id' => $order->id,
                'subtotal' => 100.00,
            ]);

            // Force rollback
            DB::rollBack();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        $this->assertEquals($initialOrderCount, Order::count());
        $this->assertEquals($initialItemCount, OrderItem::count());
        $this->assertDatabaseMissing('orders', ['order_number' => 'ROLLBACK-001']);
    }

    public function test_deleting_order_cascades_to_items(): void
    {
        $order = Order::factory()->create();

        $items = OrderItem::factory()->count(3)->create([
            'order_id' => $order->id,
        ]);

        $itemIds = $items->pluck('id')->toArray();

        $this->assertDatabaseHas('orders', ['id' => $order->id]);

        foreach ($itemIds as $itemId) {
            $this->assertDatabaseHas('order_items', ['id' => $itemId]);
        }

        $order->delete();

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);

        foreach ($itemIds as $itemId) {
            $this->assertDatabaseMissing('order_items', ['id' => $itemId]);
        }
    }

    public function test_foreign_key_constraint_prevents_orphaned_items(): void
    {
        $order = Order::factory()->create();

        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
        ]);

        $this->assertDatabaseHas('order_items', ['id' => $item->id]);

        // Attempting to delete order should cascade to items
        $order->delete();

        // Item should be deleted due to cascade
        $this->assertDatabaseMissing('order_items', ['id' => $item->id]);
    }

    public function test_order_with_multiple_items_maintains_data_integrity(): void
    {
        $order = Order::factory()->create();

        $items = collect([
            ['quantity' => 1, 'unit_price' => 10.50, 'subtotal' => 10.50],
            ['quantity' => 2, 'unit_price' => 25.00, 'subtotal' => 50.00],
            ['quantity' => 3, 'unit_price' => 15.75, 'subtotal' => 47.25],
        ]);

        foreach ($items as $itemData) {
            OrderItem::factory()->create([
                'order_id' => $order->id,
                ...$itemData,
            ]);
        }

        $order->refresh();

        $expectedTotal = $items->sum('subtotal');
        $expectedCount = $items->count();

        $this->assertEquals(
            number_format($expectedTotal, 2),
            number_format((float) $order->total_amount, 2)
        );

        $this->assertEquals($expectedCount, $order->items_count);

        // Verify each item's subtotal matches quantity * unit_price
        foreach ($order->items as $item) {
            $expectedSubtotal = round($item->quantity * (float) $item->unit_price, 2);
            $actualSubtotal = round((float) $item->subtotal, 2);

            $this->assertEquals($expectedSubtotal, $actualSubtotal);
        }
    }

    public function test_concurrent_item_additions_maintain_correct_totals(): void
    {
        $order = Order::factory()->create();

        // Simulate concurrent additions
        $item1 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'subtotal' => 100.00,
        ]);

        $order->refresh();
        $totalAfterFirst = $order->total_amount;

        $item2 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'subtotal' => 50.00,
        ]);

        $order->refresh();
        $totalAfterSecond = $order->total_amount;

        $this->assertEquals('100.00', $totalAfterFirst);
        $this->assertEquals('150.00', $totalAfterSecond);
        $this->assertEquals(2, $order->items_count);
    }

    public function test_order_relationships_are_loaded_correctly(): void
    {
        $order = Order::factory()->create();

        OrderItem::factory()->count(3)->create([
            'order_id' => $order->id,
        ]);

        $loadedOrder = Order::with('items')->find($order->id);

        $this->assertNotNull($loadedOrder);
        $this->assertTrue($loadedOrder->relationLoaded('items'));
        $this->assertCount(3, $loadedOrder->items);

        foreach ($loadedOrder->items as $item) {
            $this->assertEquals($order->id, $item->order_id);
        }
    }

    public function test_order_totals_are_consistent_after_multiple_operations(): void
    {
        $order = Order::factory()->create();

        // Add items
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

        // Update item
        $item1->update(['subtotal' => 200.00]);
        $order->refresh();
        $this->assertEquals('250.00', $order->total_amount);

        // Delete item
        $item2->delete();
        $order->refresh();
        $this->assertEquals('200.00', $order->total_amount);
        $this->assertEquals(1, $order->items_count);
    }

    public function test_order_status_transitions_maintain_data_integrity(): void
    {
        $order = Order::factory()->pending()->create();

        OrderItem::factory()->count(2)->create([
            'order_id' => $order->id,
        ]);

        $order->refresh();
        $initialTotal = $order->total_amount;
        $initialCount = $order->items_count;

        $order->update(['status' => OrderStatus::PROCESSING]);
        $order->refresh();

        $this->assertEquals(OrderStatus::PROCESSING, $order->status);
        $this->assertEquals($initialTotal, $order->total_amount);
        $this->assertEquals($initialCount, $order->items_count);

        $order->update(['status' => OrderStatus::FULFILLED]);
        $order->refresh();

        $this->assertEquals(OrderStatus::FULFILLED, $order->status);
        $this->assertEquals($initialTotal, $order->total_amount);
        $this->assertEquals($initialCount, $order->items_count);
    }
}
