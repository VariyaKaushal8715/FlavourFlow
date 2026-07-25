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
                'long_description' => 'Royal Garam Masala is a fragrant finishing blend designed to add warmth without overpowering the food. Whole spices are balanced for a rounded aroma that works across rich gravies, biryani, dals, and simple weekday sabzi.',
                'highlights' => [
                    'Slow-roasted whole-spice aroma',
                    'No artificial colours or fillers',
                    'Works as a cooking and finishing masala',
                ],
                'ingredients' => 'Coriander, cumin, black pepper, cinnamon, cloves, cardamom, bay leaf, and nutmeg.',
                'usage_instructions' => 'Add 1/2 to 1 teaspoon near the end of cooking. Adjust to taste and seal the pack after use.',
                'origin' => 'Blended and packed in Gujarat, India',
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
                'long_description' => 'A vivid red chilli powder selected for clean heat and bright natural colour. It lifts tadka, curries, marinades, and snacks while keeping the flavour direct and dependable.',
                'highlights' => [
                    'Bright natural colour',
                    'Clean, balanced heat',
                    'Finely milled for even mixing',
                ],
                'ingredients' => '100% dried red chillies.',
                'usage_instructions' => 'Use 1/4 to 1 teaspoon depending on the heat level you prefer. Store in a cool, dry place.',
                'origin' => 'Sourced and packed in India',
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
                'long_description' => 'Kitchen King Mix brings savoury depth to the dishes that appear most often on the family table. Its balanced profile makes it an easy single-blend choice for vegetables, paneer, pulao, and quick snacks.',
                'highlights' => [
                    'One blend for everyday recipes',
                    'Balanced savoury aroma',
                    'Ideal for vegetables, paneer, and rice',
                ],
                'ingredients' => 'Coriander, cumin, turmeric, chilli, black pepper, dried ginger, fenugreek, and aromatic spices.',
                'usage_instructions' => 'Add 1 to 2 teaspoons while cooking for four servings. Fry briefly with the masala base for the best aroma.',
                'origin' => 'Blended and packed in Gujarat, India',
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
