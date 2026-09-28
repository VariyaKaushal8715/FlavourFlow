<?php

use App\Models\ProductSortOption;
use App\Models\User;

test('guest cannot access admin sort options management', function () {
    $response = $this->get(route('admin.sort-options.index'));
    $response->assertRedirect(route('login'));
});

test('regular user cannot access admin sort options management', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get(route('admin.sort-options.index'));
    $response->assertForbidden();
});

test('admin can view sort options index page with all configured options', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->get(route('admin.sort-options.index'));
    $response->assertSuccessful();
    $response->assertSee('Sort Products Management');
    $response->assertSee('Featured');
    $response->assertSee('Price: Low to High');
    $response->assertSee('Newest Arrivals');
});

test('admin can create a new sort option', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->post(route('admin.sort-options.store'), [
        'label' => 'Customer Favorites',
        'key' => 'customer_favorites',
        'sort_field' => 'rating',
        'sort_direction' => 'desc',
        'display_order' => 15,
        'is_active' => true,
        'is_default' => false,
    ]);

    $response->assertRedirect(route('admin.sort-options.index'));
    $this->assertDatabaseHas('product_sort_options', [
        'key' => 'customer_favorites',
        'label' => 'Customer Favorites',
        'sort_field' => 'rating',
        'sort_direction' => 'desc',
        'display_order' => 15,
        'is_active' => true,
    ]);
});

test('admin can create sort option with auto-generated key', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->post(route('admin.sort-options.store'), [
        'label' => 'Chef Specials',
        'key' => '',
        'sort_field' => 'priority',
        'sort_direction' => 'desc',
    ]);

    $response->assertRedirect(route('admin.sort-options.index'));
    $this->assertDatabaseHas('product_sort_options', [
        'key' => 'chef_specials',
        'label' => 'Chef Specials',
    ]);
});

test('admin can edit an existing sort option', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $option = ProductSortOption::firstOrCreate(
        ['key' => 'test_edit_option'],
        [
            'label' => 'Original Label',
            'sort_field' => 'price',
            'sort_direction' => 'asc',
            'display_order' => 10,
            'is_active' => true,
        ]
    );

    $response = $this->actingAs($admin)->put(route('admin.sort-options.update', $option), [
        'label' => 'Updated Custom Label',
        'key' => 'test_edit_option_updated',
        'sort_field' => 'price',
        'sort_direction' => 'desc',
        'display_order' => 20,
        'is_active' => true,
        'is_default' => false,
    ]);

    $response->assertRedirect(route('admin.sort-options.index'));
    $this->assertDatabaseHas('product_sort_options', [
        'id' => $option->id,
        'key' => 'test_edit_option_updated',
        'label' => 'Updated Custom Label',
        'sort_direction' => 'desc',
        'display_order' => 20,
    ]);
});

test('admin can toggle active status of a sort option', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $option = ProductSortOption::firstWhere('key', 'newest');
    expect($option->is_active)->toBeTrue();

    // Toggle to inactive
    $response = $this->actingAs($admin)->patch(route('admin.sort-options.toggle', $option));
    $response->assertRedirect(route('admin.sort-options.index'));
    expect($option->fresh()->is_active)->toBeFalse();

    // Toggle back to active
    $response = $this->actingAs($admin)->patch(route('admin.sort-options.toggle', $option));
    $response->assertRedirect(route('admin.sort-options.index'));
    expect($option->fresh()->is_active)->toBeTrue();
});

test('admin can set a sort option as default', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $ratingOption = ProductSortOption::firstWhere('key', 'rating');

    $response = $this->actingAs($admin)->patch(route('admin.sort-options.set-default', $ratingOption));
    $response->assertRedirect(route('admin.sort-options.index'));

    expect($ratingOption->fresh()->is_default)->toBeTrue();
    expect($ratingOption->fresh()->is_active)->toBeTrue();

    // Ensure other options are not default
    $featuredOption = ProductSortOption::firstWhere('key', 'featured');
    expect($featuredOption->fresh()->is_default)->toBeFalse();
});

test('admin can move sort options up and down', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $first = ProductSortOption::orderBy('display_order')->first();
    $second = ProductSortOption::orderBy('display_order')->skip(1)->first();

    $firstOrder = $first->display_order;
    $secondOrder = $second->display_order;

    // Move first down
    $this->actingAs($admin)->post(route('admin.sort-options.move', $first), ['direction' => 'down']);

    expect($first->fresh()->display_order)->toBe($secondOrder);
    expect($second->fresh()->display_order)->toBe($firstOrder);
});

test('admin can batch reorder sort options', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $opt1 = ProductSortOption::firstWhere('key', 'featured');
    $opt2 = ProductSortOption::firstWhere('key', 'rating');

    $response = $this->actingAs($admin)->post(route('admin.sort-options.reorder'), [
        'orders' => [
            ['id' => $opt1->id, 'display_order' => 99],
            ['id' => $opt2->id, 'display_order' => 100],
        ],
    ]);

    $response->assertRedirect(route('admin.sort-options.index'));
    expect($opt1->fresh()->display_order)->toBe(99);
    expect($opt2->fresh()->display_order)->toBe(100);
});

test('admin can delete a sort option', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $option = ProductSortOption::create([
        'key' => 'to_delete_sort_opt',
        'label' => 'Delete Me Option',
        'sort_field' => 'price',
        'sort_direction' => 'asc',
        'display_order' => 50,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.sort-options.destroy', $option));
    $response->assertRedirect(route('admin.sort-options.index'));

    $this->assertDatabaseMissing('product_sort_options', [
        'key' => 'to_delete_sort_opt',
    ]);
});

test('admin can reset sort options to defaults', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    // Delete all options
    ProductSortOption::query()->delete();
    expect(ProductSortOption::count())->toBe(0);

    $response = $this->actingAs($admin)->post(route('admin.sort-options.reset'));
    $response->assertRedirect(route('admin.sort-options.index'));

    expect(ProductSortOption::count())->toBeGreaterThanOrEqual(8);
    expect(ProductSortOption::where('key', 'featured')->exists())->toBeTrue();
    expect(ProductSortOption::where('key', 'newest')->exists())->toBeTrue();
});
