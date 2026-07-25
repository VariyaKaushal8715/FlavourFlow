<?php

namespace App\Support;

use Illuminate\Support\Arr;

class ProductHighlightBuilder
{
    /**
     * @param  array<int, array<string, mixed>>  $products
     * @return array{featured: array<string, mixed>, supporting: array<int, array<string, mixed>>}
     */
    public function forHero(array $products): array
    {
        $rankedProducts = collect($products)
            ->map(fn (array $product): array => $this->normaliseProduct($product))
            ->filter(fn (array $product): bool => $product['is_active'] && $product['in_stock'])
            ->sortByDesc('highlight_score')
            ->values();

        return [
            'featured' => $rankedProducts->first() ?? $this->fallbackProduct(),
            'supporting' => $rankedProducts->skip(1)->take(3)->values()->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private function normaliseProduct(array $product): array
    {
        $isFeatured = (bool) Arr::get($product, 'is_featured', false);
        $priority = (int) Arr::get($product, 'priority', 0);
        $rating = (float) Arr::get($product, 'rating', 0);

        return [
            'name' => Arr::get($product, 'name', 'Featured product'),
            'category' => Arr::get($product, 'category', 'Signature product'),
            'description' => Arr::get($product, 'description', 'A highlighted product from your collection.'),
            'badge' => Arr::get($product, 'badge', $isFeatured ? 'Featured' : 'Popular'),
            'price' => Arr::get($product, 'price', ''),
            'compare_at_price' => Arr::get($product, 'compare_at_price', ''),
            'metric' => Arr::get($product, 'metric', ''),
            'image' => Arr::get($product, 'image', 'images/flavourflow-mark.png'),
            'sku' => Arr::get($product, 'sku', ''),
            'unit' => Arr::get($product, 'unit', ''),
            'quantity' => (int) Arr::get($product, 'quantity', 1),
            'stock_label' => Arr::get($product, 'stock_label', 'In stock'),
            'in_stock' => (bool) Arr::get($product, 'in_stock', true),
            'is_active' => (bool) Arr::get($product, 'is_active', true),
            'is_featured' => $isFeatured,
            'priority' => $priority,
            'rating' => $rating,
            'url' => Arr::get($product, 'url', '#products'),
            'highlight_score' => $this->highlightScore($isFeatured, $priority, $rating),
        ];
    }

    private function highlightScore(bool $isFeatured, int $priority, float $rating): float
    {
        return ($isFeatured ? 1000 : 0) + ($priority * 10) + $rating;
    }

    /**
     * @return array<string, mixed>
     */
    private function fallbackProduct(): array
    {
        return [
            'name' => 'Signature Blend',
            'category' => 'Featured product',
            'description' => 'Your best product will appear here when products are added.',
            'badge' => 'Featured',
            'price' => '',
            'compare_at_price' => '',
            'metric' => '',
            'image' => 'images/flavourflow-mark.png',
            'sku' => '',
            'unit' => '',
            'quantity' => 0,
            'stock_label' => 'Coming soon',
            'in_stock' => true,
            'is_active' => true,
            'is_featured' => true,
            'priority' => 0,
            'rating' => 0,
            'url' => '#products',
            'highlight_score' => 1000,
        ];
    }
}
