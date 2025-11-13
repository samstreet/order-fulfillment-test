<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Data Transfer Object for creating an order item.
 *
 * This immutable DTO represents a single line item within an order,
 * containing product details and pricing information.
 *
 * PHP 8.4 EXPERIMENTAL: Demonstrating property hooks (for exploration only)
 */
final class CreateOrderItemDTO
{
    // PHP 8.4 EXPERIMENTAL: Property hooks for validation
    public string $productName {
        set(string $value) {
            if (empty(trim($value))) {
                throw new \InvalidArgumentException('Product name cannot be empty');
            }
            $this->productName = $value;
        }
    }

    public int $quantity {
        set(int $value) {
            if ($value < 1) {
                throw new \InvalidArgumentException('Quantity must be at least 1');
            }
            if ($value > 9999) {
                throw new \InvalidArgumentException('Quantity cannot exceed 9999');
            }
            $this->quantity = $value;
        }
    }

    public float $unitPrice {
        set(float $value) {
            if ($value < 0.01) {
                throw new \InvalidArgumentException('Unit price must be at least 0.01');
            }
            if ($value > 999999.99) {
                throw new \InvalidArgumentException('Unit price cannot exceed 999999.99');
            }
            $this->unitPrice = $value;
        }
    }

    /**
     * @param string $productName The name of the product
     * @param int $quantity The quantity of the product ordered
     * @param float $unitPrice The price per unit of the product
     */
    public function __construct(
        string $productName,
        int $quantity,
        float $unitPrice,
    ) {
        $this->productName = $productName;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
    }

    /**
     * Create a DTO instance from an associative array.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            productName: $data['product_name'],
            quantity: $data['quantity'],
            unitPrice: $data['unit_price'],
        );
    }

    /**
     * Calculate the subtotal for this item.
     */
    public function calculateSubtotal(): float
    {
        return $this->quantity * $this->unitPrice;
    }
}
