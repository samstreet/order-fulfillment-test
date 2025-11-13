<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Data Transfer Object for creating a new order.
 *
 * This immutable DTO encapsulates all data required to create an order,
 * ensuring type safety and data integrity throughout the application.
 *
 * PHP 8.4 EXPERIMENTAL: Demonstrating property hooks (for exploration only)
 */
final class CreateOrderDTO
{
    // PHP 8.4 EXPERIMENTAL: Property hooks for validation
    public string $customerName {
        set(string $value) {
            if (empty(trim($value))) {
                throw new \InvalidArgumentException('Customer name cannot be empty');
            }
            $this->customerName = $value;
        }
    }

    public string $customerEmail {
        set(string $value) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Invalid email format');
            }
            $this->customerEmail = $value;
        }
    }

    public ?string $notes {
        set(?string $value) {
            if ($value !== null && strlen($value) > 1000) {
                throw new \InvalidArgumentException('Notes cannot exceed 1000 characters');
            }
            $this->notes = $value;
        }
    }

    public array $items {
        set(array $value) {
            // PHP 8.4: Use array_any to validate no invalid items exist
            if (array_any($value, fn($item) => !$item instanceof CreateOrderItemDTO)) {
                throw new \InvalidArgumentException('All items must be CreateOrderItemDTO instances');
            }
            $this->items = $value;
        }
    }

    /**
     * @param string $customerName The name of the customer placing the order
     * @param string $customerEmail The email address of the customer
     * @param string|null $notes Optional notes or special instructions for the order
     * @param array<int, CreateOrderItemDTO> $items Array of order items
     */
    public function __construct(
        string $customerName,
        string $customerEmail,
        ?string $notes = null,
        array $items = [],
    ) {
        $this->customerName = $customerName;
        $this->customerEmail = $customerEmail;
        $this->notes = $notes;
        $this->items = $items;
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
