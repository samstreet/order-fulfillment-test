<?php

declare(strict_types=1);

namespace Tests\Unit\DataTransferObjects;

use App\DataTransferObjects\CreateOrderItemDTO;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for CreateOrderItemDTO.
 */
class CreateOrderItemDTOTest extends TestCase
{
    public function test_it_can_be_created_with_constructor(): void
    {
        $dto = new CreateOrderItemDTO(
            productName: 'Test Product',
            quantity: 5,
            unitPrice: 29.99
        );

        $this->assertSame('Test Product', $dto->productName);
        $this->assertSame(5, $dto->quantity);
        $this->assertSame(29.99, $dto->unitPrice);
    }

    public function test_it_can_be_created_from_array(): void
    {
        $data = [
            'product_name' => 'Widget',
            'quantity' => 3,
            'unit_price' => 12.50,
        ];

        $dto = CreateOrderItemDTO::fromArray($data);

        $this->assertSame('Widget', $dto->productName);
        $this->assertSame(3, $dto->quantity);
        $this->assertSame(12.50, $dto->unitPrice);
    }

    public function test_it_calculates_subtotal_correctly(): void
    {
        $dto = new CreateOrderItemDTO(
            productName: 'Product',
            quantity: 4,
            unitPrice: 25.00
        );

        $this->assertSame(100.0, $dto->calculateSubtotal());
    }

    public function test_it_calculates_subtotal_with_decimal_unit_price(): void
    {
        $dto = new CreateOrderItemDTO(
            productName: 'Product',
            quantity: 3,
            unitPrice: 19.99
        );

        $this->assertSame(59.97, $dto->calculateSubtotal());
    }

    public function test_it_is_readonly(): void
    {
        $dto = new CreateOrderItemDTO(
            productName: 'Test',
            quantity: 1,
            unitPrice: 10.00
        );

        $this->expectException(\Error::class);
        $dto->productName = 'New Name';
    }
}
