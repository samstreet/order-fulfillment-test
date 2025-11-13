<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $orderedAt = fake()->dateTimeBetween('-90 days', 'now');
        $status = fake()->randomElement(OrderStatus::cases());

        $fulfilledAt = match ($status) {
            OrderStatus::FULFILLED => fake()->dateTimeBetween($orderedAt, 'now'),
            default => null,
        };

        return [
            'order_number' => 'ORD-' . strtoupper(fake()->unique()->bothify('??####')),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'status' => $status,
            'total_amount' => fake()->randomFloat(2, 20, 5000),
            'items_count' => fake()->numberBetween(1, 10),
            'notes' => fake()->boolean(30) ? fake()->sentence() : null,
            'ordered_at' => $orderedAt,
            'fulfilled_at' => $fulfilledAt,
        ];
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PENDING,
            'fulfilled_at' => null,
        ]);
    }

    /**
     * Indicate that the order is processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PROCESSING,
            'fulfilled_at' => null,
        ]);
    }

    /**
     * Indicate that the order is fulfilled.
     */
    public function fulfilled(): static
    {
        return $this->state(function (array $attributes) {
            $orderedAt = $attributes['ordered_at'] ?? now()->subDays(7);

            return [
                'status' => OrderStatus::FULFILLED,
                'fulfilled_at' => fake()->dateTimeBetween($orderedAt, 'now'),
            ];
        });
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::CANCELLED,
            'fulfilled_at' => null,
        ]);
    }
}
