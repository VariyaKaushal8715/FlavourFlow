<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

it('shows only the new customer form on the registration page', function (): void {
    $this->get(route('register'))
        ->assertSuccessful()
        ->assertSee('New customer')
        ->assertSee('Already have an account?')
        ->assertSee('Sign in here')
        ->assertDontSee('Existing customer');
});

it('shows only the existing customer form on the login page', function (): void {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('Existing customer')
        ->assertSee('New customer?')
        ->assertSee('Create an account')
        ->assertDontSee('New customer</span>', false);
});

it('registers a new user with a hashed password and logs them in', function (): void {
    $response = $this->post(route('register.submit'), [
        'full_name' => 'Asha Patel',
        'username' => 'asha_spice',
        'email' => 'asha@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect(route('home'));
    $this->assertAuthenticated();

    $user = User::query()->where('email', 'asha@example.com')->firstOrFail();

    expect($user->name)->toBe('Asha Patel');
    expect($user->username)->toBe('asha_spice');
    expect(Hash::check('Password123!', $user->password))->toBeTrue();
});

it('lets a user sign in with their username', function (): void {
    $user = User::factory()->create([
        'username' => 'mint_masala',
        'email' => 'mint@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->post(route('login.submit'), [
        'login' => 'mint_masala',
        'password' => 'Password123!',
    ]);

    $response->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($user);
});

it('lets a user sign in with their email address', function (): void {
    $user = User::factory()->create([
        'username' => 'saffron_lane',
        'email' => 'saffron@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->post(route('login.submit'), [
        'login' => 'saffron@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($user);
});

it('rejects duplicate usernames and emails during registration', function (): void {
    User::factory()->create([
        'username' => 'cardamom_user',
        'email' => 'cardamom@example.com',
    ]);

    $response = $this->post(route('register.submit'), [
        'full_name' => 'Another User',
        'username' => 'cardamom_user',
        'email' => 'cardamom@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSessionHasErrors(['username', 'email']);
    $this->assertGuest();
});

it('sends a password reset link for a registered email address', function (): void {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'forgot@example.com',
    ]);

    $response = $this->post(route('password.email'), [
        'email' => $user->email,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});
