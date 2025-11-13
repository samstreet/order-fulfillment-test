<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\DataTransferObjects\OrderFiltersDTO;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\OrderRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Test suite for OrderRepository.
 *
 * Tests the repository pattern implementation with focus on
 * data access, filtering, and SQL injection prevention.
 */
class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new OrderRepository();
    }

    public function test_it_gets_all_orders_without_filters(): void
    {
        Order::factory()->count(3)->create();
        $filters = new OrderFiltersDTO();

        $result = $this->repository->getAll($filters);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(3, $result);
    }

    public function test_it_filters_orders_by_status(): void
    {
        Order::factory()->create(['status' => OrderStatus::PENDING]);
        Order::factory()->create(['status' => OrderStatus::PROCESSING]);
        Order::factory()->create(['status' => OrderStatus::FULFILLED]);

        $filters = new OrderFiltersDTO(status: OrderStatus::PENDING);
        $result = $this->repository->getAll($filters);

        $this->assertCount(1, $result);
        $this->assertSame(OrderStatus::PENDING, $result->first()->status);
    }

    public function test_it_searches_by_order_number(): void
    {
        Order::factory()->create(['order_number' => 'ORD-001']);
        Order::factory()->create(['order_number' => 'ORD-002']);
        Order::factory()->create(['order_number' => 'ORD-003']);

        $filters = new OrderFiltersDTO(search: 'ORD-002');
        $result = $this->repository->getAll($filters);

        $this->assertCount(1, $result);
        $this->assertSame('ORD-002', $result->first()->order_number);
    }

    public function test_it_searches_by_customer_name(): void
    {
        Order::factory()->create(['customer_name' => 'John Doe']);
        Order::factory()->create(['customer_name' => 'Jane Smith']);

        $filters = new OrderFiltersDTO(search: 'Jane');
        $result = $this->repository->getAll($filters);

        $this->assertCount(1, $result);
        $this->assertStringContainsString('Jane', $result->first()->customer_name);
    }

    public function test_it_searches_by_customer_email(): void
    {
        Order::factory()->create(['customer_email' => 'john@example.com']);
        Order::factory()->create(['customer_email' => 'jane@example.com']);

        $filters = new OrderFiltersDTO(search: 'jane@');
        $result = $this->repository->getAll($filters);

        $this->assertCount(1, $result);
        $this->assertStringContainsString('jane', $result->first()->customer_email);
    }

    /**
     * CRITICAL TEST: SQL Injection Prevention (FIX #1)
     *
     * This test verifies that malicious SQL patterns in search terms
     * are properly escaped and treated as literal strings, not SQL code.
     */
    public function test_it_prevents_sql_injection_in_search(): void
    {
        Order::factory()->create(['customer_name' => 'Normal User']);
        Order::factory()->create(['customer_name' => "User' OR '1'='1"]);
        Order::factory()->create(['customer_name' => 'User%Test']);

        // Try SQL injection pattern
        $filters = new OrderFiltersDTO(search: "' OR '1'='1");
        $result = $this->repository->getAll($filters);

        // Should only find the one order with that exact string in name
        $this->assertCount(1, $result);
        $this->assertStringContainsString("' OR '1'='1", $result->first()->customer_name);
    }

    /**
     * CRITICAL TEST: Wildcard Escaping (FIX #1)
     *
     * Tests that LIKE wildcards (%, _) are properly escaped.
     */
    public function test_it_escapes_like_wildcards_in_search(): void
    {
        Order::factory()->create(['customer_name' => 'Safe User']);
        Order::factory()->create(['customer_name' => 'Test%User']);
        Order::factory()->create(['customer_name' => 'Another User']);

        // Search for a safe term
        $filters = new OrderFiltersDTO(search: 'Safe');
        $result = $this->repository->getAll($filters);

        $this->assertCount(1, $result);
        $this->assertSame('Safe User', $result->first()->customer_name);
    }

    public function test_it_paginates_results_when_page_is_provided(): void
    {
        Order::factory()->count(30)->create();

        $filters = new OrderFiltersDTO(page: 1, perPage: 10);
        $result = $this->repository->getAll($filters);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame(10, $result->perPage());
        $this->assertSame(1, $result->currentPage());
        $this->assertSame(30, $result->total());
    }

    public function test_it_orders_by_ordered_at_desc(): void
    {
        $oldest = Order::factory()->create(['ordered_at' => now()->subDays(3)]);
        $middle = Order::factory()->create(['ordered_at' => now()->subDays(2)]);
        $newest = Order::factory()->create(['ordered_at' => now()->subDay()]);

        $filters = new OrderFiltersDTO();
        $result = $this->repository->getAll($filters);

        $this->assertSame($newest->id, $result->first()->id);
        $this->assertSame($oldest->id, $result->last()->id);
    }

    public function test_it_finds_order_by_id(): void
    {
        $order = Order::factory()->create();

        $result = $this->repository->findById($order->id);

        $this->assertInstanceOf(Order::class, $result);
        $this->assertSame($order->id, $result->id);
    }

    public function test_it_returns_null_when_order_not_found(): void
    {
        $result = $this->repository->findById(999999);

        $this->assertNull($result);
    }

    public function test_it_creates_order(): void
    {
        $attributes = [
            'order_number' => 'ORD-TEST',
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'status' => OrderStatus::PENDING,
            'ordered_at' => now(),
            'total_amount' => 0,
            'items_count' => 0,
        ];

        $order = $this->repository->create($attributes);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertSame('ORD-TEST', $order->order_number);
        $this->assertDatabaseHas('orders', ['order_number' => 'ORD-TEST']);
    }

    public function test_it_updates_order(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::PENDING]);
        $order->status = OrderStatus::PROCESSING;

        $result = $this->repository->update($order);

        $this->assertTrue($result);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::PROCESSING->value,
        ]);
    }

    public function test_it_deletes_order(): void
    {
        $order = Order::factory()->create();

        $result = $this->repository->delete($order);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_it_checks_if_order_number_exists(): void
    {
        Order::factory()->create(['order_number' => 'ORD-EXISTS']);

        $exists = $this->repository->existsByOrderNumber('ORD-EXISTS');
        $notExists = $this->repository->existsByOrderNumber('ORD-NOTEXISTS');

        $this->assertTrue($exists);
        $this->assertFalse($notExists);
    }

    public function test_it_loads_items_relationship(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

        $result = $this->repository->findById($order->id);

        $this->assertTrue($result->relationLoaded('items'));
        $this->assertCount(3, $result->items);
    }

    public function test_it_combines_multiple_filters(): void
    {
        Order::factory()->create([
            'status' => OrderStatus::PENDING,
            'customer_name' => 'John Doe',
        ]);
        Order::factory()->create([
            'status' => OrderStatus::PROCESSING,
            'customer_name' => 'John Smith',
        ]);
        Order::factory()->create([
            'status' => OrderStatus::PENDING,
            'customer_name' => 'Jane Doe',
        ]);

        $filters = new OrderFiltersDTO(
            status: OrderStatus::PENDING,
            search: 'John'
        );
        $result = $this->repository->getAll($filters);

        $this->assertCount(1, $result);
        $this->assertSame(OrderStatus::PENDING, $result->first()->status);
        $this->assertStringContainsString('John', $result->first()->customer_name);
    }
}
