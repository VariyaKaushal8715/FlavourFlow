<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

test('the admin url shows a private login to guests', function () {
    $this->get(route('admin.index'))
        ->assertSuccessful()
        ->assertSee('Restricted access')
        ->assertSee('Sign in to admin');
});

test('a non admin user cannot open the admin dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.index'))
        ->assertForbidden();
});

test('an administrator can sign in from the admin url', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'owner@flavourflow.test',
        'password' => 'secure-password',
    ]);

    $this->post(route('admin.login'), [
        'email' => $admin->email,
        'password' => 'secure-password',
    ])->assertRedirect(route('admin.index'));

    $this->assertAuthenticatedAs($admin);
});

test('an administrator can add a product and make it the homepage spotlight', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $previousFeaturedProduct = Product::factory()->featured()->create();

    $this->actingAs($admin)
        ->post(route('admin.products.store'), [
            'name' => 'Smoked Kashmiri Chilli',
            'sku' => 'ff-sc-100',
            'category' => 'Pure spice',
            'unit' => '100 g',
            'description' => 'Deep natural colour with a rounded warmth for curries and marinades.',
            'badge' => 'New arrival',
            'price' => '189.00',
            'compare_at_price' => '219.00',
            'quantity' => '75',
            'low_stock_threshold' => '10',
            'rating' => '4.9',
            'priority' => '98',
            'image' => fakeProductImage('kashmiri-chilli.png'),
            'is_featured' => '1',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.index'))
        ->assertSessionHas('status');

    $product = Product::query()->where('name', 'Smoked Kashmiri Chilli')->firstOrFail();

    $this->assertModelExists($product);
    expect($product->is_featured)->toBeTrue()
        ->and($product->sku)->toBe('FF-SC-100')
        ->and($product->quantity)->toBe(75)
        ->and($previousFeaturedProduct->fresh()->is_featured)->toBeFalse();

    Storage::disk('public')->assertExists(Str::after($product->image_path, 'storage/'));

    $this->get(route('admin.index'))
        ->assertSuccessful()
        ->assertSee('Smoked Kashmiri Chilli')
        ->assertSee('Products');

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Smoked Kashmiri Chilli')
        ->assertDontSee('Admin Login');
});

test('an administrator can update product pricing inventory and image', function () {
    Storage::fake('public');
    Storage::disk('public')->put('products/old-product.jpg', 'old-image');

    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create([
        'name' => 'Classic Turmeric',
        'sku' => 'FF-TU-100',
        'image_path' => 'storage/products/old-product.jpg',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.products.edit', $product))
        ->assertSuccessful()
        ->assertSee('Classic Turmeric')
        ->assertSee('Update product');

    $this
        ->put(route('admin.products.update', $product), [
            'name' => 'Lakadong Turmeric',
            'sku' => 'ff-lt-100',
            'category' => 'Pure spice',
            'unit' => '100 g',
            'description' => 'High-curcumin turmeric with a deep golden colour and earthy aroma.',
            'badge' => 'Premium',
            'price' => '199.00',
            'compare_at_price' => '229.00',
            'quantity' => '4',
            'low_stock_threshold' => '6',
            'rating' => '4.8',
            'priority' => '90',
            'image' => fakeProductImage('lakadong.png'),
            'is_featured' => '1',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.index'))
        ->assertSessionHas('status');

    $product->refresh();

    expect($product->name)->toBe('Lakadong Turmeric')
        ->and($product->sku)->toBe('FF-LT-100')
        ->and($product->quantity)->toBe(4)
        ->and($product->isLowStock())->toBeTrue()
        ->and($product->is_featured)->toBeTrue();

    Storage::disk('public')->assertMissing('products/old-product.jpg');
    Storage::disk('public')->assertExists(Str::after($product->image_path, 'storage/'));
});

test('a non admin user cannot delete a product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)
        ->delete(route('admin.products.destroy', $product))
        ->assertForbidden();

    $this->assertModelExists($product);
});

test('deleting a product permanently removes its database row and uploaded image', function () {
    Storage::fake('public');
    Storage::disk('public')->put('products/delete-me.jpg', 'image');

    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create([
        'name' => 'Delete Me Masala',
        'image_path' => 'storage/products/delete-me.jpg',
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.products.destroy', $product))
        ->assertRedirect(route('admin.index'))
        ->assertSessionHas('status');

    $this->assertModelMissing($product);
    Storage::disk('public')->assertMissing('products/delete-me.jpg');

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('Delete Me Masala');
});

test('the product dashboard can search filter and report inventory', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->lowStock()->create([
        'name' => 'Low Stock Masala',
        'sku' => 'FF-LOW-1',
    ]);
    Product::factory()->outOfStock()->create([
        'name' => 'Sold Out Masala',
        'sku' => 'FF-OUT-1',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.index', ['search' => 'FF-LOW-1']))
        ->assertSuccessful()
        ->assertSee('Low Stock Masala')
        ->assertDontSee('Sold Out Masala');

    $this->get(route('admin.index', ['status' => 'out_of_stock']))
        ->assertSuccessful()
        ->assertSee('Sold Out Masala')
        ->assertDontSee('Low Stock Masala');
});

test('the storefront does not expose template instructions', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('The collection')
        ->assertDontSee('Built as a reusable base')
        ->assertDontSee('How you customise this template')
        ->assertDontSee('Ready for your details')
        ->assertDontSee('Make this homepage yours');
});

function fakeProductImage(string $name): UploadedFile
{
    $contents = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
        true,
    );

    assert(is_string($contents));

    return UploadedFile::fake()->createWithContent($name, $contents);
}
