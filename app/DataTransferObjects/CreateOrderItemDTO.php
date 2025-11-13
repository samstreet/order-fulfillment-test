<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Data Transfer Object for creating an order item.
 *
 * This immutable DTO represents a single line item within an order,
 * containing product details and pricing information.
 */
final readonly class CreateOrderItemDTO
{
    /**
     * @param string $productName The name of the product
     * @param int $quantity The quantity of the product ordered
     * @param float $unitPrice The price per unit of the product
     */
    public function __construct(
        public string $productName,
        public int $quantity,
        public float $unitPrice,
    ) {
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
