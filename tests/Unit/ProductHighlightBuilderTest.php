<?php

use App\Support\ProductHighlightBuilder;

test('featured products are selected for the hero highlight first', function () {
    $showcase = (new ProductHighlightBuilder)->forHero([
        [
            'name' => 'High Priority Product',
            'is_featured' => false,
            'is_active' => true,
            'priority' => 100,
            'rating' => 5,
        ],
        [
            'name' => 'Featured Product',
            'is_featured' => true,
            'is_active' => true,
            'priority' => 1,
            'rating' => 1,
        ],
    ]);

    expect($showcase['featured']['name'])->toBe('Featured Product')
        ->and($showcase['supporting'])->toHaveCount(1);
});

test('priority and rating rank products when none are featured', function () {
    $showcase = (new ProductHighlightBuilder)->forHero([
        [
            'name' => 'Lower Product',
            'is_featured' => false,
            'is_active' => true,
            'priority' => 10,
            'rating' => 4.9,
        ],
        [
            'name' => 'Higher Product',
            'is_featured' => false,
            'is_active' => true,
            'priority' => 11,
            'rating' => 4.1,
        ],
    ]);

    expect($showcase['featured']['name'])->toBe('Higher Product');
});
