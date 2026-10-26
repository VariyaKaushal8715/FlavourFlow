<?php

use App\Models\User;
use App\Models\UserProfile;

test('profile page renders mobile account overview and toggleable edit form container', function (): void {
    $user = User::factory()->create([
        'name' => 'Aarav Sharma',
        'email' => 'aarav@example.com',
    ]);

    UserProfile::factory()->for($user)->create([
        'full_name' => 'Aarav Sharma',
        'mobile_number' => '+91 98765 43210',
        'email' => 'aarav@example.com',
        'address' => '42 Spices Avenue, Bodakdev',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'country' => 'India',
        'postal_code' => '380054',
    ]);

    $response = $this->actingAs($user)
        ->get(route('account.profile'));

    $response->assertSuccessful();

    // Verify mobile overview elements
    $response->assertSee('id="mobile-profile-overview"', false);
    $response->assertSee('id="open-mobile-profile-edit"', false);
    $response->assertSee('Edit Personal Details');
    $response->assertSee('42 Spices Avenue, Bodakdev');
    $response->assertSee('+91 98765 43210');
    $response->assertSee('Ahmedabad, Gujarat, India, 380054');

    // Verify form container and cancel buttons
    $response->assertSee('id="profile-form-container"', false);
    $response->assertSee('class="mobile-profile-cancel', false);
    $response->assertSee('Cancel');

    // Verify all form fields are preserved
    $response->assertSee('name="full_name"', false);
    $response->assertSee('name="mobile_number"', false);
    $response->assertSee('name="email"', false);
    $response->assertSee('name="address"', false);
    $response->assertSee('name="city"', false);
    $response->assertSee('name="state"', false);
    $response->assertSee('name="country"', false);
    $response->assertSee('name="postal_code"', false);
});

test('mobile navigation panel renders streamlined links and language selector', function (): void {
    $response = $this->get(route('home'));

    $response->assertSuccessful();

    // Check mobile panel and toggle button
    $response->assertSee('id="site-nav-panel"', false);
    $response->assertSee('data-site-nav-toggle', false);
    $response->assertSee('data-site-nav-panel', false);

    // Language switch links
    $response->assertSee(route('language.switch', 'en'));
    $response->assertSee(route('language.switch', 'gu'));
    $response->assertSee(route('language.switch', 'hi'));

    // Guest auth controls
    $response->assertSee(route('login'));
    $response->assertSee(route('register'));
});

test('mobile navigation panel shows dedicated admin dashboard link for authenticated admins', function (): void {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin@flavourflow.com',
        'is_admin' => true,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('home'));

    $response->assertSuccessful();

    // Verify admin link is rendered for admin
    $response->assertSee(route('admin.index'));
    $response->assertSee('Admin Dashboard');
    $response->assertSee('Admin Access');
});

test('mobile navigation panel does not expose admin link to regular authenticated customers', function (): void {
    $customer = User::factory()->create([
        'name' => 'Regular Customer',
        'email' => 'customer@example.com',
        'is_admin' => false,
    ]);

    $response = $this->actingAs($customer)
        ->get(route('home'));

    $response->assertSuccessful();

    // Verify customer sees account links without duplicates
    $response->assertSee(route('account.orders'));
    $response->assertSee(route('account.coupons'));
    $response->assertSee(route('account.profile'));
    $response->assertSee(route('logout'));

    // Should not contain admin dashboard link
    $response->assertDontSee(route('admin.index'));
});
