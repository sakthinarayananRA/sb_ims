<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'code' => 'PRD-' . fake()->unique()->numerify('####'),
            'price_per_unit' => fake()->randomFloat(2, 10, 500),
            'tax_percentage' => fake()->randomElement([0.00, 5.00, 8.00, 12.00, 18.00]),
            'stock_on_hand' => fake()->numberBetween(5, 100),
            'low_stock_threshold' => 10,
        ];
    }

    /**
     * Indicate that the product has low stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_on_hand' => fake()->numberBetween(1, 4),
            'low_stock_threshold' => 10,
        ]);
    }
}

