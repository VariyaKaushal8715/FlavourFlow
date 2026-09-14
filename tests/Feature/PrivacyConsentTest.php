<?php

use App\Models\PrivacyConsent;
use App\Models\User;

test('an authenticated user without consent sees the privacy modal', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Choose your cookie preference')
        ->assertSee('Allow Cookies & Activity Tracking', false)
        ->assertSee('Decline Non-Essential Tracking')
        ->assertSee(route('privacy-policy'));
});

test('allowing activity tracking saves the preference and suppresses the modal', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('privacy-consent.store'), ['consent_status' => 'allowed'])
        ->assertRedirect();

    $user->refresh();

    expect($user->privacyConsent)->not->toBeNull()
        ->and($user->allowsActivityTracking())->toBeTrue();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertDontSee('Choose your cookie preference');

    $this->assertDatabaseHas('privacy_consents', [
        'user_id' => $user->id,
        'consent_status' => PrivacyConsent::STATUS_ALLOWED,
        'activity_tracking' => true,
        'consent_version' => PrivacyConsent::VERSION,
    ]);
});

test('declining activity tracking keeps essential pages working and blocks tracking permission', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('privacy-consent.store'), ['consent_status' => 'declined'])
        ->assertRedirect();

    $user->refresh();

    expect($user->allowsActivityTracking())->toBeFalse();

    $this->actingAs($user)
        ->get(route('account.profile'))
        ->assertSuccessful()
        ->assertSee('Privacy & Cookies', false)
        ->assertSee('Declined');
});

test('a user can revoke optional tracking from their own profile', function (): void {
    $user = User::factory()->create();
    PrivacyConsent::query()->create([
        'user_id' => $user->id,
        'consent_status' => PrivacyConsent::STATUS_ALLOWED,
        'activity_tracking' => true,
        'consent_version' => PrivacyConsent::VERSION,
        'consented_at' => now(),
    ]);

    $this->actingAs($user)
        ->put(route('privacy-consent.update'), ['consent_status' => 'declined'])
        ->assertRedirect();

    expect($user->fresh()->allowsActivityTracking())->toBeFalse();
});

test('consent updates cannot modify another users preference', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    PrivacyConsent::query()->create([
        'user_id' => $otherUser->id,
        'consent_status' => PrivacyConsent::STATUS_DECLINED,
        'activity_tracking' => false,
        'consent_version' => PrivacyConsent::VERSION,
        'consented_at' => now(),
    ]);

    $this->actingAs($user)
        ->put(route('privacy-consent.update'), [
            'user_id' => $otherUser->id,
            'consent_status' => 'allowed',
        ])
        ->assertRedirect();

    expect($otherUser->fresh()->allowsActivityTracking())->toBeFalse();
    expect($user->fresh()->allowsActivityTracking())->toBeTrue();
});

test('privacy policy is publicly accessible', function (): void {
    $this->get(route('privacy-policy'))
        ->assertSuccessful()
        ->assertSee('Privacy Policy')
        ->assertSee('Optional activity tracking and AI features')
        ->assertSee('full card numbers, CVV, PINs, OTPs');
});

test('privacy consent endpoints require authentication', function (): void {
    $this->post(route('privacy-consent.store'), ['consent_status' => 'allowed'])
        ->assertRedirect(route('login'));

    $this->put(route('privacy-consent.update'), ['consent_status' => 'allowed'])
        ->assertRedirect(route('login'));
});
