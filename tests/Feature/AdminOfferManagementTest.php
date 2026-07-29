<?php

use App\Models\Offer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

test('only administrators can manage offers', function () {
    $offer = Offer::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.offers.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('admin.offers.destroy', $offer))
        ->assertForbidden();

    $this->assertModelExists($offer);
});

test('an administrator can create a featured offer that appears on the homepage', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $previousFeaturedOffer = Offer::factory()->featured()->create();

    $this->actingAs($admin)
        ->post(route('admin.offers.store'), [
            'eyebrow' => 'Festival special',
            'title' => 'Celebrate with a flavour bundle',
            'description' => 'A limited selection of festive blends for family cooking.',
            'discount_label' => 'Save 25%',
            'coupon_code' => 'festive25',
            'terms' => 'Valid above Rs. 599.',
            'starts_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'priority' => 99,
            'image' => fakeOfferImage('festival.png'),
            'is_featured' => '1',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.offers.index'))
        ->assertSessionHas('status');

    $offer = Offer::query()->where('title', 'Celebrate with a flavour bundle')->firstOrFail();

    expect($offer->coupon_code)->toBe('FESTIVE25')
        ->and($offer->is_featured)->toBeTrue()
        ->and($offer->isCurrentlyVisible())->toBeTrue()
        ->and($previousFeaturedOffer->fresh()->is_featured)->toBeFalse();

    Storage::disk('public')->assertExists(Str::after($offer->image_path, 'storage/'));

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Celebrate with a flavour bundle')
        ->assertSee('FESTIVE25');
});

test('an administrator can update and permanently delete an offer', function () {
    Storage::fake('public');
    Storage::disk('public')->put('offers/old.png', 'old-image');

    $admin = User::factory()->admin()->create();
    $offer = Offer::factory()->create([
        'title' => 'Old offer title',
        'image_path' => 'storage/offers/old.png',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.offers.update', $offer), [
            'eyebrow' => 'Today only',
            'title' => 'Updated offer title',
            'description' => 'Updated campaign copy for today.',
            'discount_label' => 'Flat Rs. 100 off',
            'coupon_code' => 'today100',
            'terms' => 'Valid today.',
            'starts_at' => '',
            'ends_at' => '',
            'priority' => 90,
            'image' => fakeOfferImage('updated.png'),
            'is_featured' => '1',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.offers.index'));

    $offer->refresh();

    expect($offer->title)->toBe('Updated offer title')
        ->and($offer->coupon_code)->toBe('TODAY100')
        ->and($offer->is_featured)->toBeTrue();

    Storage::disk('public')->assertMissing('offers/old.png');
    Storage::disk('public')->assertExists(Str::after($offer->image_path, 'storage/'));

    $uploadedImagePath = $offer->uploadedImageStoragePath();

    $this->actingAs($admin)
        ->delete(route('admin.offers.destroy', $offer))
        ->assertRedirect(route('admin.offers.index'));

    $this->assertModelMissing($offer);
    Storage::disk('public')->assertMissing($uploadedImagePath);
});

test('expired and inactive offers stay off the storefront', function () {
    Offer::factory()->create([
        'title' => 'Expired offer',
        'starts_at' => now()->subWeek(),
        'ends_at' => now()->subDay(),
    ]);
    Offer::factory()->inactive()->create([
        'title' => 'Inactive offer',
    ]);
    Offer::factory()->create([
        'title' => 'Live offer',
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Live offer')
        ->assertDontSee('Expired offer')
        ->assertDontSee('Inactive offer');
});

function fakeOfferImage(string $name): UploadedFile
{
    $contents = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
        true,
    );

    assert(is_string($contents));

    return UploadedFile::fake()->createWithContent($name, $contents);
}
