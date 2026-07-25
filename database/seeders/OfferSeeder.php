<?php

namespace Database\Seeders;

use App\Models\Offer;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $offers = [
            [
                'eyebrow' => 'Monsoon special',
                'title' => 'Warm up rainy-day cooking',
                'description' => 'Save on bold masalas made for pakoras, chai-time snacks, curries, and comforting family meals.',
                'discount_label' => 'Save 20%',
                'coupon_code' => 'MONSOON20',
                'terms' => 'Valid on orders above Rs. 499 while stocks last.',
                'priority' => 95,
                'image_path' => 'images/flavourflow-spice-hero.png',
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'eyebrow' => "Today's special",
                'title' => 'Everyday essentials, better value',
                'description' => 'Pick any three daily-use spices and make your kitchen shelf ready for the week.',
                'discount_label' => 'Pick 3 & save',
                'coupon_code' => 'DAILY3',
                'terms' => 'Offer applies to selected pure spices.',
                'priority' => 80,
                'image_path' => 'images/flavourflow-hero.png',
                'is_featured' => false,
                'is_active' => true,
            ],
        ];

        foreach ($offers as $offer) {
            Offer::query()->updateOrCreate(
                ['title' => $offer['title']],
                $offer,
            );
        }
    }
}
