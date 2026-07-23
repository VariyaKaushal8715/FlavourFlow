<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Royal Garam Masala',
                'sku' => 'FF-GM-100',
                'category' => 'Signature blend',
                'unit' => '100 g',
                'description' => 'A warm, layered masala built for curries, gravies, biryani, and everyday home cooking.',
                'badge' => 'Hero pick',
                'price' => 149,
                'compare_at_price' => 179,
                'quantity' => 120,
                'low_stock_threshold' => 15,
                'rating' => 4.9,
                'priority' => 95,
                'image_path' => 'images/flavourflow-mark.png',
                'is_featured' => true,
            ],
            [
                'name' => 'Red Chilli Powder',
                'sku' => 'FF-RC-100',
                'category' => 'Pure spice',
                'unit' => '100 g',
                'description' => 'Bright colour, clean heat, and a bold finish for daily recipes.',
                'badge' => 'Best seller',
                'price' => 99,
                'compare_at_price' => 119,
                'quantity' => 86,
                'low_stock_threshold' => 12,
                'rating' => 4.8,
                'priority' => 88,
                'image_path' => 'images/flavourflow-spice-hero.png',
                'is_featured' => false,
            ],
            [
                'name' => 'Kitchen King Mix',
                'sku' => 'FF-KK-100',
                'category' => 'Everyday blend',
                'unit' => '100 g',
                'description' => 'A balanced spice profile for sabzi, snacks, and quick family meals.',
                'badge' => 'Popular',
                'price' => 129,
                'compare_at_price' => 149,
                'quantity' => 48,
                'low_stock_threshold' => 10,
                'rating' => 4.7,
                'priority' => 82,
                'image_path' => 'images/flavourflow-hero.png',
                'is_featured' => false,
            ],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['slug' => Str::slug($product['name'])],
                [...$product, 'is_active' => true],
            );
        }
    }
}
