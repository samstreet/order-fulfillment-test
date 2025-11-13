<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes_are_correct(): void
    {
        $fillable = (new OrderItem())->getFillable();

        $expectedFillable = [
            'order_id',
            'product_name',
            'quantity',
            'unit_price',
            'subtotal',
        ];

        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_casts_are_configured_correctly(): void
    {
        $item = new OrderItem();

        $this->assertArrayHasKey('order_id', $item->getCasts());
        $this->assertArrayHasKey('quantity', $item->getCasts());
        $this->assertArrayHasKey('unit_price', $item->getCasts());
        $this->assertArrayHasKey('subtotal', $item->getCasts());
    }

    public function test_order_id_cast_to_integer(): void
    {
        $order = Order::factory()->create();
        $item = OrderItem::factory()->create(['order_id' => $order->id]);

        $this->assertIsInt($item->order_id);
    }

    public function test_quantity_cast_to_integer(): void
    {
        $item = OrderItem::factory()->create(['quantity' => 5]);

        $this->assertIsInt($item->quantity);
    }

    public function test_unit_price_cast_to_decimal(): void
    {
        $item = OrderItem::factory()->create(['unit_price' => 99.99]);

        $this->assertEquals('99.99', $item->unit_price);
    }

    public function test_subtotal_cast_to_decimal(): void
    {
        $item = OrderItem::factory()->create(['subtotal' => 199.98]);

        $this->assertEquals('199.98', $item->subtotal);
    }

    public function test_order_relationship_returns_belongs_to(): void
    {
        $item = new OrderItem();

        $this->assertInstanceOf(BelongsTo::class, $item->order());
    }

    public function test_order_relationship_returns_correct_order(): void
    {
        $order = Order::factory()->create();
        $item = OrderItem::factory()->create(['order_id' => $order->id]);

        $this->assertInstanceOf(Order::class, $item->order);
        $this->assertEquals($order->id, $item->order->id);
    }

    public function test_formatted_unit_price_accessor(): void
    {
        $item = OrderItem::factory()->create(['unit_price' => 49.99]);

        $this->assertEquals('$49.99', $item->formatted_unit_price);
    }

    public function test_formatted_subtotal_accessor(): void
    {
        $item = OrderItem::factory()->create(['subtotal' => 149.97]);

        $this->assertEquals('$149.97', $item->formatted_subtotal);
    }

    public function test_formatted_accessors_handle_large_numbers(): void
    {
        $item = OrderItem::factory()->create([
            'unit_price' => 1234.56,
            'subtotal' => 2469.12,
        ]);

        $this->assertEquals('$1,234.56', $item->formatted_unit_price);
        $this->assertEquals('$2,469.12', $item->formatted_subtotal);
    }

    public function test_formatted_accessors_handle_zero_values(): void
    {
        $item = OrderItem::factory()->create([
            'unit_price' => 0.00,
            'subtotal' => 0.00,
        ]);

        $this->assertEquals('$0.00', $item->formatted_unit_price);
        $this->assertEquals('$0.00', $item->formatted_subtotal);
    }
}
