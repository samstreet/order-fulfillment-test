<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Data Transfer Object for creating a new order.
 *
 * This immutable DTO encapsulates all data required to create an order,
 * ensuring type safety and data integrity throughout the application.
 */
final readonly class CreateOrderDTO
{
    /**
     * @param string $customerName The name of the customer placing the order
     * @param string $customerEmail The email address of the customer
     * @param string|null $notes Optional notes or special instructions for the order
     * @param array<int, CreateOrderItemDTO> $items Array of order items
     */
    public function __construct(
        public string $customerName,
        public string $customerEmail,
        public ?string $notes = null,
        public array $items = [],
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
        $items = [];
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                if (is_array($item)) {
                    $items[] = CreateOrderItemDTO::fromArray($item);
                }
            }
        }

        return new self(
            customerName: $data['customer_name'],
            customerEmail: $data['customer_email'],
            notes: $data['notes'] ?? null,
            items: $items,
        );
    }
}
