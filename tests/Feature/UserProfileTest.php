<?php

use App\Models\User;
use App\Models\UserProfile;

test('guests are redirected to login before opening the account profile page', function (): void {
    $this->get(route('account.profile'))
        ->assertRedirect(route('login'));
});

test('authenticated users can store and update only their own profile details', function (): void {
    $alice = User::factory()->create([
        'name' => 'Asha Patel',
        'email' => 'asha@example.com',
    ]);

    $bob = User::factory()->create([
        'name' => 'Kabir Shah',
        'email' => 'kabir@example.com',
    ]);

    $this->actingAs($alice)
        ->post(route('account.profile.store'), [
            'full_name' => 'Asha Patel',
            'mobile_number' => '9876543210',
            'email' => 'asha@example.com',
            'address' => '12 Spice Lane',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'country' => 'India',
            'postal_code' => '380001',
        ])
        ->assertRedirect(route('account.profile'));

    $aliceProfile = UserProfile::query()->whereBelongsTo($alice)->firstOrFail();

    expect($aliceProfile->full_name)->toBe('Asha Patel')
        ->and($aliceProfile->city)->toBe('Ahmedabad')
        ->and($aliceProfile->postal_code)->toBe('380001');

    $this->actingAs($alice)
        ->put(route('account.profile.update'), [
            'full_name' => 'Asha Patel',
            'mobile_number' => '9999999999',
            'email' => 'asha.orders@example.com',
            'address' => '48 Pepper Court',
            'city' => 'Surat',
            'state' => 'Gujarat',
            'country' => 'India',
            'postal_code' => '395003',
        ])
        ->assertRedirect(route('account.profile'));

    $aliceProfile->refresh();

    expect($aliceProfile->mobile_number)->toBe('9999999999')
        ->and($aliceProfile->email)->toBe('asha.orders@example.com')
        ->and($aliceProfile->city)->toBe('Surat');

    $this->actingAs($bob)
        ->post(route('account.profile.store'), [
            'full_name' => 'Kabir Shah',
            'mobile_number' => '9123456780',
            'email' => 'kabir@example.com',
            'address' => '9 Turmeric Street',
            'city' => 'Rajkot',
            'state' => 'Gujarat',
            'country' => 'India',
            'postal_code' => '360001',
        ])
        ->assertRedirect(route('account.profile'));

    expect(UserProfile::query()->whereBelongsTo($bob)->firstOrFail()->city)->toBe('Rajkot')
        ->and(UserProfile::query()->whereBelongsTo($alice)->firstOrFail()->city)->toBe('Surat');
});

test('authenticated users can delete their own profile details', function (): void {
    $user = User::factory()->create();

    UserProfile::factory()->for($user)->create([
        'full_name' => 'Meera Desai',
        'mobile_number' => '9000000000',
        'email' => 'meera@example.com',
        'address' => '77 Saffron Road',
        'city' => 'Vadodara',
        'state' => 'Gujarat',
        'country' => 'India',
        'postal_code' => '390001',
    ]);

    $this->actingAs($user)
        ->delete(route('account.profile.destroy'))
        ->assertRedirect(route('account.profile'));

    $this->assertDatabaseMissing('user_profiles', [
        'user_id' => $user->getKey(),
    ]);
});
