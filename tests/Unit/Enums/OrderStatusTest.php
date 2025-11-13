<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\OrderStatus;
use PHPUnit\Framework\TestCase;

final class OrderStatusTest extends TestCase
{
    public function test_has_all_expected_enum_values(): void
    {
        $values = OrderStatus::values();

        $this->assertCount(4, $values);
        $this->assertContains('pending', $values);
        $this->assertContains('processing', $values);
        $this->assertContains('fulfilled', $values);
        $this->assertContains('cancelled', $values);
    }

    public function test_pending_status_has_correct_label(): void
    {
        $this->assertSame('Pending', OrderStatus::PENDING->label());
    }

    public function test_processing_status_has_correct_label(): void
    {
        $this->assertSame('Processing', OrderStatus::PROCESSING->label());
    }

    public function test_fulfilled_status_has_correct_label(): void
    {
        $this->assertSame('Fulfilled', OrderStatus::FULFILLED->label());
    }

    public function test_cancelled_status_has_correct_label(): void
    {
        $this->assertSame('Cancelled', OrderStatus::CANCELLED->label());
    }

    public function test_pending_status_has_correct_color(): void
    {
        $this->assertSame('yellow', OrderStatus::PENDING->color());
    }

    public function test_processing_status_has_correct_color(): void
    {
        $this->assertSame('blue', OrderStatus::PROCESSING->color());
    }

    public function test_fulfilled_status_has_correct_color(): void
    {
        $this->assertSame('green', OrderStatus::FULFILLED->color());
    }

    public function test_cancelled_status_has_correct_color(): void
    {
        $this->assertSame('red', OrderStatus::CANCELLED->color());
    }

    public function test_values_returns_array_of_strings(): void
    {
        $values = OrderStatus::values();

        foreach ($values as $value) {
            $this->assertIsString($value);
        }
    }

    public function test_all_cases_have_labels(): void
    {
        foreach (OrderStatus::cases() as $status) {
            $this->assertIsString($status->label());
            $this->assertNotEmpty($status->label());
        }
    }

    public function test_all_cases_have_colors(): void
    {
        foreach (OrderStatus::cases() as $status) {
            $this->assertIsString($status->color());
            $this->assertNotEmpty($status->color());
        }
    }
}
