<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Items directly featured in the Wireframe layout
            [
                'name' => 'Colgate Toothpaste',
                'code' => 'PRD-COLG-01',
                'price_per_unit' => 50.00,
                'tax_percentage' => 8.00,
                'stock_on_hand' => 25,
                'low_stock_threshold' => 10,
            ],
            [
                'name' => 'Parle-G Biscuit',
                'code' => 'PRD-PARL-01',
                'price_per_unit' => 10.00,
                'tax_percentage' => 8.00,
                'stock_on_hand' => 50,
                'low_stock_threshold' => 10,
            ],
            // Low Stock Alert items featured in the wireframe
            [
                'name' => 'Bread',
                'code' => 'PRD-BRED-01',
                'price_per_unit' => 40.00,
                'tax_percentage' => 5.00,
                'stock_on_hand' => 4,
                'low_stock_threshold' => 10,
            ],
            [
                'name' => 'Milk 1L',
                'code' => 'PRD-MILK-01',
                'price_per_unit' => 60.00,
                'tax_percentage' => 0.00,
                'stock_on_hand' => 9,
                'low_stock_threshold' => 10,
            ],
            [
                'name' => 'Eggs (12)',
                'code' => 'PRD-EGGS-01',
                'price_per_unit' => 84.00,
                'tax_percentage' => 0.00,
                'stock_on_hand' => 2,
                'low_stock_threshold' => 10,
            ],
            // Supporting grocery store products
            [
                'name' => 'Tata Salt 1kg',
                'code' => 'PRD-SALT-01',
                'price_per_unit' => 28.00,
                'tax_percentage' => 5.00,
                'stock_on_hand' => 60,
                'low_stock_threshold' => 10,
            ],
            [
                'name' => 'Fortune Sunflower Oil 1L',
                'code' => 'PRD-FOIL-01',
                'price_per_unit' => 165.00,
                'tax_percentage' => 5.00,
                'stock_on_hand' => 30,
                'low_stock_threshold' => 10,
            ],
            [
                'name' => 'Nescafe Classic Coffee 50g',
                'code' => 'PRD-NESC-01',
                'price_per_unit' => 180.00,
                'tax_percentage' => 18.00,
                'stock_on_hand' => 20,
                'low_stock_threshold' => 5,
            ],
            [
                'name' => 'Maggi 2-Minute Noodles',
                'code' => 'PRD-MAGG-01',
                'price_per_unit' => 14.00,
                'tax_percentage' => 12.00,
                'stock_on_hand' => 85,
                'low_stock_threshold' => 15,
            ],
            [
                'name' => 'India Gate Basmati Rice 5kg',
                'code' => 'PRD-RICE-01',
                'price_per_unit' => 450.00,
                'tax_percentage' => 5.00,
                'stock_on_hand' => 15,
                'low_stock_threshold' => 5,
            ],
            [
                'name' => 'Dettol Bathing Soap',
                'code' => 'PRD-DETT-01',
                'price_per_unit' => 45.00,
                'tax_percentage' => 18.00,
                'stock_on_hand' => 40,
                'low_stock_threshold' => 10,
            ],
            [
                'name' => 'Surf Excel Detergent 1kg',
                'code' => 'PRD-SURF-01',
                'price_per_unit' => 140.00,
                'tax_percentage' => 18.00,
                'stock_on_hand' => 30,
                'low_stock_threshold' => 10,
            ],
        ];

        foreach ($products as $productData) {
            $stock = $productData['stock_on_hand'];
            $product = Product::updateOrCreate(
                ['code' => $productData['code']],
                $productData
            );

            // Audit trail for initial stock
            $product->stockMovements()->create([
                'type' => 'initial',
                'quantity_change' => $stock,
                'stock_after' => $stock,
                'remarks' => 'Initial inventory opening stock',
            ]);
        }
    }
}

