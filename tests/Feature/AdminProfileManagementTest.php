<?php

use App\Models\AdminProfile;
use App\Models\User;

test('guests are redirected from admin profile page', function (): void {
    $this->get(route('admin.profile.edit'))
        ->assertRedirect(route('login'));
});

test('non-admin users cannot access admin profile page', function (): void {
    $customer = User::factory()->create([
        'is_admin' => false,
    ]);

    $this->actingAs($customer)
        ->get(route('admin.profile.edit'))
        ->assertForbidden();
});

test('admin can view admin profile page with fallback prefilled data', function (): void {
    $admin = User::factory()->admin()->create([
        'name' => 'Kaushal Admin',
        'email' => 'kaushal.admin@flavourflow.com',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.profile.edit'));

    $response->assertSuccessful();
    $response->assertSee('Admin Profile');
    $response->assertSee('Kaushal Admin');
    $response->assertSee('kaushal.admin@flavourflow.com');
});

test('admin can create and update their profile details successfully and reload on refresh', function (): void {
    $admin = User::factory()->admin()->create([
        'name' => 'Original Admin',
        'email' => 'original@flavourflow.com',
    ]);

    $response = $this->actingAs($admin)
        ->put(route('admin.profile.update'), [
            'full_name' => 'Kaushal Variya',
            'email' => 'kaushal.updated@flavourflow.com',
            'mobile_number' => '+91 98765 43210',
            'address' => '42 Spice Boulevard, Suite 100',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'country' => 'India',
            'postal_code' => '380015',
            'date_of_birth' => '1995-06-15',
            'gender' => 'male',
        ]);

    $response->assertRedirect(route('admin.profile.edit'));
    $response->assertSessionHas('status', 'Your admin profile details have been updated successfully.');

    $this->assertDatabaseHas('admin_profiles', [
        'user_id' => $admin->id,
        'full_name' => 'Kaushal Variya',
        'email' => 'kaushal.updated@flavourflow.com',
        'mobile_number' => '+91 98765 43210',
        'address' => '42 Spice Boulevard, Suite 100',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'country' => 'India',
        'postal_code' => '380015',
        'gender' => 'male',
    ]);

    $profile = AdminProfile::where('user_id', $admin->id)->firstOrFail();
    expect($profile->date_of_birth->toDateString())->toBe('1995-06-15');

    // Check user account synchronization
    $admin->refresh();
    expect($admin->name)->toBe('Kaushal Variya')
        ->and($admin->email)->toBe('kaushal.updated@flavourflow.com');

    // Test page reload displays newly saved data
    $reloadResponse = $this->actingAs($admin)
        ->get(route('admin.profile.edit'));

    $reloadResponse->assertSuccessful();
    $reloadResponse->assertSee('Kaushal Variya');
    $reloadResponse->assertSee('kaushal.updated@flavourflow.com');
    $reloadResponse->assertSee('+91 98765 43210');
    $reloadResponse->assertSee('42 Spice Boulevard, Suite 100');
    $reloadResponse->assertSee('Ahmedabad');
    $reloadResponse->assertSee('380015');
});

test('admin cannot set email to another registered user email', function (): void {
    $existingUser = User::factory()->create(['email' => 'taken@flavourflow.com']);
    $admin = User::factory()->admin()->create(['email' => 'admin@flavourflow.com']);

    $this->actingAs($admin)
        ->from(route('admin.profile.edit'))
        ->put(route('admin.profile.update'), [
            'full_name' => 'Admin Name',
            'email' => 'taken@flavourflow.com',
        ])
        ->assertRedirect(route('admin.profile.edit'))
        ->assertSessionHasErrors(['email']);
});

test('profile updates are validated properly', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->from(route('admin.profile.edit'))
        ->put(route('admin.profile.update'), [
            'full_name' => '',
            'email' => 'not-an-email',
            'mobile_number' => 'invalid-phone-letters',
            'date_of_birth' => '2099-01-01',
            'gender' => 'invalid-gender',
        ])
        ->assertRedirect(route('admin.profile.edit'))
        ->assertSessionHasErrors([
            'full_name',
            'email',
            'mobile_number',
            'date_of_birth',
            'gender',
        ]);
});
