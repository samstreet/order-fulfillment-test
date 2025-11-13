<?php

declare(strict_types=1);

namespace Tests\Unit\DataTransferObjects;

use App\DataTransferObjects\CreateOrderDTO;
use App\DataTransferObjects\CreateOrderItemDTO;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for CreateOrderDTO.
 */
class CreateOrderDTOTest extends TestCase
{
    public function test_it_can_be_created_with_constructor(): void
    {
        $dto = new CreateOrderDTO(
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
            notes: 'Test notes',
            items: []
        );

        $this->assertSame('John Doe', $dto->customerName);
        $this->assertSame('john@example.com', $dto->customerEmail);
        $this->assertSame('Test notes', $dto->notes);
        $this->assertIsArray($dto->items);
        $this->assertEmpty($dto->items);
    }

    public function test_it_can_be_created_from_array(): void
    {
        $data = [
            'customer_name' => 'Jane Smith',
            'customer_email' => 'jane@example.com',
            'notes' => 'Special instructions',
        ];

        $dto = CreateOrderDTO::fromArray($data);

        $this->assertSame('Jane Smith', $dto->customerName);
        $this->assertSame('jane@example.com', $dto->customerEmail);
        $this->assertSame('Special instructions', $dto->notes);
        $this->assertEmpty($dto->items);
    }

    public function test_it_can_be_created_from_array_without_notes(): void
    {
        $data = [
            'customer_name' => 'Bob Johnson',
            'customer_email' => 'bob@example.com',
        ];

        $dto = CreateOrderDTO::fromArray($data);

        $this->assertSame('Bob Johnson', $dto->customerName);
        $this->assertSame('bob@example.com', $dto->customerEmail);
        $this->assertNull($dto->notes);
        $this->assertEmpty($dto->items);
    }

    public function test_it_can_be_created_from_array_with_items(): void
    {
        $data = [
            'customer_name' => 'Alice Williams',
            'customer_email' => 'alice@example.com',
            'notes' => 'Order with items',
            'items' => [
                [
                    'product_name' => 'Product A',
                    'quantity' => 2,
                    'unit_price' => 19.99,
                ],
                [
                    'product_name' => 'Product B',
                    'quantity' => 1,
                    'unit_price' => 49.99,
                ],
            ],
        ];

        $dto = CreateOrderDTO::fromArray($data);

        $this->assertSame('Alice Williams', $dto->customerName);
        $this->assertSame('alice@example.com', $dto->customerEmail);
        $this->assertSame('Order with items', $dto->notes);
        $this->assertCount(2, $dto->items);
        $this->assertContainsOnlyInstancesOf(CreateOrderItemDTO::class, $dto->items);
        $this->assertSame('Product A', $dto->items[0]->productName);
        $this->assertSame(2, $dto->items[0]->quantity);
        $this->assertSame(19.99, $dto->items[0]->unitPrice);
    }

    public function test_it_handles_empty_items_array(): void
    {
        $data = [
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'items' => [],
        ];

        $dto = CreateOrderDTO::fromArray($data);

        $this->assertEmpty($dto->items);
    }

    public function test_property_hooks_validate_input(): void
    {
        // Test valid construction
        $dto = new CreateOrderDTO(
            customerName: 'Test User',
            customerEmail: 'test@example.com'
        );

        $this->assertSame('Test User', $dto->customerName);
        $this->assertSame('test@example.com', $dto->customerEmail);

        // Test property hook validation - empty name
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer name cannot be empty');
        $dto->customerName = '';
    }

    public function test_property_hooks_validate_email(): void
    {
        $dto = new CreateOrderDTO(
            customerName: 'Test User',
            customerEmail: 'test@example.com'
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');
        $dto->customerEmail = 'invalid-email';
    }

    public function test_property_hooks_validate_notes_length(): void
    {
        $dto = new CreateOrderDTO(
            customerName: 'Test User',
            customerEmail: 'test@example.com'
        );

        $longNotes = str_repeat('a', 1001);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Notes cannot exceed 1000 characters');
        $dto->notes = $longNotes;
    }

    public function test_property_hooks_validate_items_array(): void
    {
        $dto = new CreateOrderDTO(
            customerName: 'Test User',
            customerEmail: 'test@example.com'
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All items must be CreateOrderItemDTO instances');
        $dto->items = ['invalid item'];
    }
}
