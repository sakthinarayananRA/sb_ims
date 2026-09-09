<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . strtoupper(fake()->unique()->bothify('####??')),
            'customer_id' => Customer::factory(),
            'status' => 'completed',
            'subtotal' => 0.00,
            'tax_amount' => 0.00,
            'grand_total' => 0.00,
            'paid_amount' => 0.00,
            'change_amount' => 0.00,
        ];
    }
}

