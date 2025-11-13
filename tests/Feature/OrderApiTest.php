<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_orders_returns_successful_response(): void
    {
        Order::factory()->count(3)->create();

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'order_number',
                        'customer_name',
                        'customer_email',
                        'status',
                        'total_amount',
                        'items_count',
                        'ordered_at',
                    ]
                ]
            ]);
    }

    public function test_get_single_order_returns_order(): void
    {
        $order = Order::factory()->create();

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'customer_email' => $order->customer_email,
                ]
            ]);
    }

    public function test_get_nonexistent_order_returns_404(): void
    {
        $response = $this->getJson('/api/orders/999');

        $response->assertStatus(404);
    }

    public function test_update_order_status_works(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::PENDING]);

        $response = $this->patchJson("/api/orders/{$order->id}/status", [
            'status' => OrderStatus::PROCESSING->value,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => [
                        'value' => OrderStatus::PROCESSING->value,
                    ]
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::PROCESSING,
        ]);
    }

    public function test_delete_order_works(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::PENDING]);

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order deleted successfully',
            ]);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }
}
