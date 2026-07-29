<?php

use App\Models\User;

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
