<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes_are_correct(): void
    {
        $fillable = (new Order())->getFillable();

        $expectedFillable = [
            'order_number',
            'customer_name',
            'customer_email',
            'status',
            'notes',
            'ordered_at',
            'fulfilled_at',
        ];

        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_total_amount_is_not_fillable(): void
    {
        $fillable = (new Order())->getFillable();

        $this->assertNotContains('total_amount', $fillable);
    }

    public function test_items_count_is_not_fillable(): void
    {
        $fillable = (new Order())->getFillable();

        $this->assertNotContains('items_count', $fillable);
    }

    public function test_casts_are_configured_correctly(): void
    {
        $order = new Order();

        $this->assertArrayHasKey('status', $order->getCasts());
        $this->assertArrayHasKey('total_amount', $order->getCasts());
        $this->assertArrayHasKey('items_count', $order->getCasts());
        $this->assertArrayHasKey('ordered_at', $order->getCasts());
        $this->assertArrayHasKey('fulfilled_at', $order->getCasts());
    }

    public function test_status_cast_to_order_status_enum(): void
    {
        $order = Order::factory()->pending()->create();

        $this->assertInstanceOf(OrderStatus::class, $order->status);
        $this->assertEquals(OrderStatus::PENDING, $order->status);
    }

    public function test_items_relationship_returns_has_many(): void
    {
        $order = new Order();

        $this->assertInstanceOf(HasMany::class, $order->items());
    }

    public function test_items_relationship_returns_order_items(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

        $this->assertCount(3, $order->items);
        $this->assertInstanceOf(OrderItem::class, $order->items->first());
    }

    public function test_scope_by_status_filters_orders_correctly(): void
    {
        Order::factory()->pending()->count(2)->create();
        Order::factory()->processing()->count(3)->create();
        Order::factory()->fulfilled()->count(1)->create();

        $pendingOrders = Order::byStatus(OrderStatus::PENDING)->get();
        $processingOrders = Order::byStatus(OrderStatus::PROCESSING)->get();
        $fulfilledOrders = Order::byStatus(OrderStatus::FULFILLED)->get();

        $this->assertCount(2, $pendingOrders);
        $this->assertCount(3, $processingOrders);
        $this->assertCount(1, $fulfilledOrders);
    }

    public function test_scope_recent_filters_orders_by_date(): void
    {
        Order::factory()->create(['ordered_at' => now()->subDays(10)]);
        Order::factory()->create(['ordered_at' => now()->subDays(40)]);
        Order::factory()->create(['ordered_at' => now()->subDays(5)]);

        $recentOrders = Order::recent(30)->get();

        $this->assertCount(2, $recentOrders);
    }

    public function test_scope_recent_with_custom_days(): void
    {
        Order::factory()->create(['ordered_at' => now()->subDays(5)]);
        Order::factory()->create(['ordered_at' => now()->subDays(15)]);
        Order::factory()->create(['ordered_at' => now()->subDays(25)]);

        $recentOrders = Order::recent(10)->get();

        $this->assertCount(1, $recentOrders);
    }

    public function test_formatted_total_amount_accessor(): void
    {
        $order = Order::factory()->create();

        // Create items to set the total
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'subtotal' => 123.45,
        ]);

        $order->refresh();

        $this->assertEquals('$123.45', $order->formatted_total_amount);
    }

    public function test_formatted_items_count_accessor_singular(): void
    {
        $order = Order::factory()->create();

        OrderItem::factory()->create([
            'order_id' => $order->id,
        ]);

        $order->refresh();

        $this->assertEquals('1 item', $order->formatted_items_count);
    }

    public function test_formatted_items_count_accessor_plural(): void
    {
        $order = Order::factory()->create();

        OrderItem::factory()->count(5)->create([
            'order_id' => $order->id,
        ]);

        $order->refresh();

        $this->assertEquals('5 items', $order->formatted_items_count);
    }

    public function test_order_totals_are_calculated_automatically_on_creation(): void
    {
        $order = Order::factory()->create();

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 2,
            'unit_price' => 50.00,
            'subtotal' => 100.00,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 1,
            'unit_price' => 25.00,
            'subtotal' => 25.00,
        ]);

        $order->refresh();

        $this->assertEquals('125.00', $order->total_amount);
        $this->assertEquals(2, $order->items_count);
    }
}
