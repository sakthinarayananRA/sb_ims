<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Wireframe reference customer
        Customer::firstOrCreate(
            ['email' => 'thomas@example.com'],
            [
                'name' => 'Thomas Anderson',
                'phone' => '+91 98765 43210',
            ]
        );

        // Additional sample customers
        $customers = [
            ['name' => 'Priya Sharma', 'email' => 'priya.sharma@example.com', 'phone' => '+91 98765 11111'],
            ['name' => 'Rahul Verma', 'email' => 'rahul.verma@example.com', 'phone' => '+91 98765 22222'],
            ['name' => 'Ananya Iyer', 'email' => 'ananya.iyer@example.com', 'phone' => '+91 98765 33333'],
            ['name' => 'Deepak Patel', 'email' => 'deepak.patel@example.com', 'phone' => '+91 98765 44444'],
            ['name' => 'Sneha Kulkarni', 'email' => 'sneha.k@example.com', 'phone' => '+91 98765 55555'],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(['email' => $customer['email']], $customer);
        }

        // Generate 5 more via factory
        Customer::factory()->count(5)->create();
    }
}

