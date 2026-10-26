<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;

test('admin login and dashboard responses cannot be restored from browser cache', function () {
    $admin = User::factory()->admin()->create();

    $loginResponse = $this->get(route('admin.index'))
        ->assertSuccessful()
        ->assertHeader('Pragma', 'no-cache')
        ->assertHeader('Expires', '0');

    $dashboardResponse = $this->actingAs($admin)
        ->get(route('admin.index'))
        ->assertSuccessful()
        ->assertSee('Products')
        ->assertDontSee('Restricted access');

    expect($loginResponse->headers->get('Cache-Control'))
        ->toContain('no-store')
        ->toContain('must-revalidate')
        ->toContain('private')
        ->and($dashboardResponse->headers->get('Cache-Control'))
        ->toContain('no-store')
        ->toContain('must-revalidate')
        ->toContain('private');
});

test('logging out invalidates the session and keeps the redirect out of cache', function () {
    $admin = User::factory()->admin()->create();

    $logoutResponse = $this->actingAs($admin)
        ->post(route('admin.logout'))
        ->assertRedirect(route('admin.index'));

    expect($logoutResponse->headers->get('Cache-Control'))
        ->toContain('no-store')
        ->toContain('must-revalidate')
        ->toContain('private');

    $this->assertGuest();

    $this->get(route('admin.index'))
        ->assertSuccessful()
        ->assertSee('Restricted access')
        ->assertDontSee('Catalog, inventory, and offers');
});

test('admin can log in using environment configured credentials seeded by AdminUserSeeder', function () {
    $this->seed(AdminUserSeeder::class);

    $response = $this->post(route('admin.login'), [
        'email' => config('admin.email'),
        'password' => config('admin.password'),
    ]);

    $response->assertRedirect(route('admin.index'));
    $this->assertAuthenticated();
    expect(auth()->user()->is_admin)->toBeTrue();
});

test('admin can log in using environment credentials on the fly without prior seeder run', function () {
    // Ensure no admin user exists
    User::where('email', config('admin.email'))->delete();

    $response = $this->post(route('admin.login'), [
        'email' => config('admin.email'),
        'password' => config('admin.password'),
    ]);

    $response->assertRedirect(route('admin.index'));
    $this->assertAuthenticated();
    $admin = auth()->user();
    expect($admin->is_admin)->toBeTrue()
        ->and($admin->email)->toBe(config('admin.email'));
});

test('admin login ignores previous customer url.intended and always directs to admin dashboard', function () {
    $this->seed(AdminUserSeeder::class);

    $response = $this->withSession(['url.intended' => route('account.profile')])
        ->post(route('admin.login'), [
            'email' => config('admin.email'),
            'password' => config('admin.password'),
        ]);

    // Must redirect to admin.index, NOT account.profile!
    $response->assertRedirect(route('admin.index'));
    expect(session()->has('url.intended'))->toBeFalse();
});

test('logging in from standard storefront login form with admin account redirects directly to admin dashboard', function () {
    $admin = User::factory()->admin()->create([
        'password' => 'SecurePass123!',
    ]);

    $response = $this->post(route('login'), [
        'login' => $admin->email,
        'password' => 'SecurePass123!',
    ]);

    $response->assertRedirect(route('admin.index'));
});

test('non-admin customer visiting /admin is forbidden', function () {
    $customer = User::factory()->create([
        'is_admin' => false,
    ]);

    $response = $this->actingAs($customer)
        ->get(route('admin.index'));

    $response->assertForbidden();
});
