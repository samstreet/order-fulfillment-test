<?php

declare(strict_types=1);

namespace Tests\Unit\DataTransferObjects;

use App\DataTransferObjects\OrderFiltersDTO;
use App\Enums\OrderStatus;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for OrderFiltersDTO.
 */
class OrderFiltersDTOTest extends TestCase
{
    public function test_it_can_be_created_with_constructor(): void
    {
        $dto = new OrderFiltersDTO(
            status: OrderStatus::PENDING,
            search: 'test',
            page: 1,
            perPage: 20
        );

        $this->assertSame(OrderStatus::PENDING, $dto->status);
        $this->assertSame('test', $dto->search);
        $this->assertSame(1, $dto->page);
        $this->assertSame(20, $dto->perPage);
    }

    public function test_it_can_be_created_with_nulls(): void
    {
        $dto = new OrderFiltersDTO();

        $this->assertNull($dto->status);
        $this->assertNull($dto->search);
        $this->assertNull($dto->page);
        $this->assertNull($dto->perPage);
    }

    public function test_it_can_be_created_from_array_with_all_filters(): void
    {
        $filters = [
            'status' => 'processing',
            'search' => 'john',
            'page' => 2,
            'per_page' => 25,
        ];

        $dto = OrderFiltersDTO::fromArray($filters);

        $this->assertSame(OrderStatus::PROCESSING, $dto->status);
        $this->assertSame('john', $dto->search);
        $this->assertSame(2, $dto->page);
        $this->assertSame(25, $dto->perPage);
    }

    public function test_it_can_be_created_from_empty_array(): void
    {
        $dto = OrderFiltersDTO::fromArray([]);

        $this->assertNull($dto->status);
        $this->assertNull($dto->search);
        $this->assertNull($dto->page);
        $this->assertNull($dto->perPage);
    }

    public function test_it_handles_partial_filters(): void
    {
        $filters = [
            'status' => 'fulfilled',
            'page' => 3,
        ];

        $dto = OrderFiltersDTO::fromArray($filters);

        $this->assertSame(OrderStatus::FULFILLED, $dto->status);
        $this->assertNull($dto->search);
        $this->assertSame(3, $dto->page);
        $this->assertNull($dto->perPage);
    }

    public function test_it_checks_if_paginated_returns_true_when_page_is_set(): void
    {
        $dto = new OrderFiltersDTO(page: 1);

        $this->assertTrue($dto->isPaginated());
    }

    public function test_it_checks_if_paginated_returns_false_when_page_is_null(): void
    {
        $dto = new OrderFiltersDTO();

        $this->assertFalse($dto->isPaginated());
    }

    public function test_it_gets_per_page_with_custom_value(): void
    {
        $dto = new OrderFiltersDTO(perPage: 50);

        $this->assertSame(50, $dto->getPerPage());
    }

    public function test_it_gets_per_page_with_default_value(): void
    {
        $dto = new OrderFiltersDTO();

        $this->assertSame(15, $dto->getPerPage());
    }

    public function test_it_gets_per_page_with_custom_default(): void
    {
        $dto = new OrderFiltersDTO();

        $this->assertSame(30, $dto->getPerPage(30));
    }

    public function test_it_is_readonly(): void
    {
        $dto = new OrderFiltersDTO(search: 'test');

        $this->expectException(\Error::class);
        $dto->search = 'new value';
    }
}
