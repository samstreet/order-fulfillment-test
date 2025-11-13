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

    public function test_filter_orders_by_status(): void
    {
        Order::factory()->count(2)->create(['status' => OrderStatus::PENDING]);
        Order::factory()->count(3)->create(['status' => OrderStatus::PROCESSING]);

        $response = $this->getJson('/api/orders?status=' . OrderStatus::PENDING->value);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_search_orders_by_customer_name(): void
    {
        Order::factory()->create(['customer_name' => 'John Doe']);
        Order::factory()->create(['customer_name' => 'Jane Smith']);

        $response = $this->getJson('/api/orders?search=John');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['customer_name' => 'John Doe']);
    }

    public function test_pagination_works(): void
    {
        Order::factory()->count(25)->create();

        $response = $this->getJson('/api/orders?page=1&per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ]
            ]);
    }

    public function test_create_order_works(): void
    {
        $orderData = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'notes' => 'Test order',
            'items' => [
                [
                    'product_name' => 'Test Product',
                    'quantity' => 2,
                    'unit_price' => 10.50,
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'order_number',
                    'customer_name',
                    'customer_email',
                    'status',
                    'total_amount',
                    'items_count',
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
        ]);
    }

    public function test_update_order_status_validation(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::FULFILLED]);

        $response = $this->patchJson("/api/orders/{$order->id}/status", [
            'status' => OrderStatus::PROCESSING->value,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message'
            ])
            ->assertJson([
                'message' => 'Cannot transition order from fulfilled to processing'
            ]);
    }

    public function test_delete_processing_order_fails(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::PROCESSING]);

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message'
            ])
            ->assertJson([
                'message' => 'Order cannot be deleted: Order is currently being processed'
            ]);

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_api_returns_correct_data_structure(): void
    {
        $order = Order::factory()->create();
        $order->items()->create([
            'product_name' => 'Test Item',
            'quantity' => 1,
            'unit_price' => 10.00,
            'subtotal' => 10.00,
        ]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'order_number',
                    'customer_name',
                    'customer_email',
                    'status' => [
                        'value',
                        'label',
                        'color'
                    ],
                    'total_amount' => [
                        'value',
                        'formatted'
                    ],
                    'items_count' => [
                        'value',
                        'formatted'
                    ],
                    'notes',
                    'ordered_at',
                    'fulfilled_at',
                    'created_at',
                    'updated_at',
                    'items' => [
                        '*' => [
                            'id',
                            'order_id',
                            'product_name',
                            'quantity',
                            'unit_price' => [
                                'value',
                                'formatted'
                            ],
                            'subtotal' => [
                                'value',
                                'formatted'
                            ],
                            'created_at',
                            'updated_at',
                        ]
                    ]
                ]
            ]);
    }
}
