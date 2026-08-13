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

test('authenticated users can update their saved mobile number and email address separately', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $profile = UserProfile::factory()->for($user)->create([
        'mobile_number' => '9000000000',
        'email' => 'meera@example.com',
    ]);
    $otherProfile = UserProfile::factory()->for($otherUser)->create([
        'mobile_number' => '9111111111',
        'email' => 'other@example.com',
    ]);

    $this->actingAs($user)
        ->patchJson(route('account.profile.mobile_number.update'), [
            'mobile_number' => '+91 98765 43210',
        ])
        ->assertSuccessful()
        ->assertJsonPath('profile.mobile_number', '+91 98765 43210')
        ->assertJsonPath('message', 'Your mobile number has been updated.');

    $this->actingAs($user)
        ->patchJson(route('account.profile.email.update'), [
            'email' => 'meera.orders@example.com',
        ])
        ->assertSuccessful()
        ->assertJsonPath('profile.email', 'meera.orders@example.com')
        ->assertJsonPath('message', 'Your email address has been updated.');

    $profile->refresh();
    $otherProfile->refresh();

    expect($profile->mobile_number)->toBe('+91 98765 43210')
        ->and($profile->email)->toBe('meera.orders@example.com')
        ->and($otherProfile->mobile_number)->toBe('9111111111')
        ->and($otherProfile->email)->toBe('other@example.com');
});

test('profile contact updates are validated before saving', function (): void {
    $user = User::factory()->create();
    $profile = UserProfile::factory()->for($user)->create([
        'mobile_number' => '9000000000',
        'email' => 'meera@example.com',
    ]);

    $this->actingAs($user)
        ->withHeader('Accept', 'application/json')
        ->patchJson(route('account.profile.mobile_number.update'), [
            'mobile_number' => 'abc',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('mobile_number');

    $this->actingAs($user)
        ->withHeader('Accept', 'application/json')
        ->patchJson(route('account.profile.email.update'), [
            'email' => 'not-an-email',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    $profile->refresh();

    expect($profile->mobile_number)->toBe('9000000000')
        ->and($profile->email)->toBe('meera@example.com');
});
