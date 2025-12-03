<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Laptop',
                'description' => 'High-performance laptop with 16GB RAM',
                'price' => 999.99,
                'stock' => 10,
                'image_url' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=500&h=500&fit=crop',
            ],
            [
                'name' => 'Smartphone',
                'description' => 'Latest smartphone with 128GB storage',
                'price' => 699.99,
                'stock' => 25,
                'image_url' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=500&h=500&fit=crop',
            ],
            [
                'name' => 'Headphones',
                'description' => 'Wireless noise-cancelling headphones',
                'price' => 199.99,
                'stock' => 50,
                'image_url' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&h=500&fit=crop',
            ],
            [
                'name' => 'Keyboard',
                'description' => 'Mechanical gaming keyboard',
                'price' => 149.99,
                'stock' => 30,
                'image_url' => 'https://images.unsplash.com/photo-1541140532154-b024d705b90a?w=500&h=500&fit=crop',
            ],
            [
                'name' => 'Mouse',
                'description' => 'Wireless ergonomic mouse',
                'price' => 79.99,
                'stock' => 40,
                'image_url' => 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500&h=500&fit=crop',
            ],
            [
                'name' => 'Monitor',
                'description' => '27-inch 4K Ultra HD monitor',
                'price' => 449.99,
                'stock' => 15,
                'image_url' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=500&h=500&fit=crop',
            ],
            [
                'name' => 'Webcam',
                'description' => '1080p HD webcam with microphone',
                'price' => 89.99,
                'stock' => 35,
                'image_url' => 'https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04?w=500&h=500&fit=crop',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
