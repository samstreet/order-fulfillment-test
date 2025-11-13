<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Available product names for realistic test data.
     *
     * @var array<int, string>
     */
    private const PRODUCTS = [
        'Wireless Mouse',
        'Mechanical Keyboard',
        'USB-C Hub',
        'External SSD 1TB',
        'Laptop Stand',
        'Webcam HD',
        'Noise Cancelling Headphones',
        'Bluetooth Speaker',
        'Phone Charging Cable',
        'Laptop Backpack',
        'Monitor 27"',
        'Desk Lamp LED',
        'Ergonomic Chair',
        'Standing Desk',
        'Mouse Pad',
        'HDMI Cable',
        'Power Bank',
        'USB Flash Drive',
        'Graphics Tablet',
        'Microphone',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $unitPrice = fake()->randomFloat(2, 10, 500);
        $subtotal = round($quantity * $unitPrice, 2);

        return [
            'order_id' => Order::factory(),
            'product_name' => fake()->randomElement(self::PRODUCTS),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
        ];
    }
}
