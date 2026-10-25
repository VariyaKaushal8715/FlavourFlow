<?php

use App\Models\User;
use App\Services\AdminAccountService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

test('admin can log in using configured .env credentials from the admin login page', function (): void {
    Config::set('admin.email', 'testadmin@flavourflow.test');
    Config::set('admin.password', 'SecretPass@123');

    $response = $this->post(route('admin.login'), [
        'email' => 'testadmin@flavourflow.test',
        'password' => 'SecretPass@123',
    ]);

    $response->assertRedirect(route('admin.index'));
    $this->assertAuthenticated();

    $user = auth()->user();
    expect($user->is_admin)->toBeTrue();
    expect($user->email)->toBe('testadmin@flavourflow.test');
});

test('admin can log in using configured .env credentials from customer login form and is redirected to admin dashboard', function (): void {
    Config::set('admin.email', 'testadmin@flavourflow.test');
    Config::set('admin.password', 'SecretPass@123');

    $response = $this->post(route('login.submit'), [
        'login' => 'testadmin@flavourflow.test',
        'password' => 'SecretPass@123',
    ]);

    $response->assertRedirect(route('admin.index'));
    $this->assertAuthenticated();
    expect(auth()->user()->is_admin)->toBeTrue();
});

test('admin can log in using admin username from customer login form', function (): void {
    Config::set('admin.email', 'testadmin@flavourflow.test');
    Config::set('admin.password', 'SecretPass@123');
    Config::set('admin.username', 'admin');

    $response = $this->post(route('login.submit'), [
        'login' => 'admin',
        'password' => 'SecretPass@123',
    ]);

    $response->assertRedirect(route('admin.index'));
    $this->assertAuthenticated();
    expect(auth()->user()->is_admin)->toBeTrue();
});

test('admin login rejects wrong password', function (): void {
    Config::set('admin.email', 'testadmin@flavourflow.test');
    Config::set('admin.password', 'SecretPass@123');

    $adminSessionResponse = $this->post(route('admin.login'), [
        'email' => 'testadmin@flavourflow.test',
        'password' => 'WrongPassword!',
    ]);

    $adminSessionResponse->assertSessionHasErrors('email');
    $this->assertGuest();

    $userSessionResponse = $this->post(route('login.submit'), [
        'login' => 'testadmin@flavourflow.test',
        'password' => 'WrongPassword!',
    ]);

    $userSessionResponse->assertSessionHasErrors('login');
    $this->assertGuest();
});

test('admin session gives access to admin dashboard and guest is prompted with login', function (): void {
    Config::set('admin.email', 'testadmin@flavourflow.test');
    Config::set('admin.password', 'SecretPass@123');

    $admin = AdminAccountService::sync();

    // Guest visiting admin index sees login form
    $this->get(route('admin.index'))
        ->assertSuccessful()
        ->assertSee('Sign in with an administrator account')
        ->assertSee('Sign in to admin');

    // Authenticated admin can view dashboard
    $this->actingAs($admin)
        ->get(route('admin.index'))
        ->assertSuccessful()
        ->assertSee('Dashboard')
        ->assertDontSee('Sign in to admin');
});

test('admin can log out from admin session and session is invalidated', function (): void {
    Config::set('admin.email', 'testadmin@flavourflow.test');
    Config::set('admin.password', 'SecretPass@123');

    $admin = AdminAccountService::sync();

    $response = $this->actingAs($admin)->post(route('admin.logout'));

    $response->assertRedirect(route('admin.index'));
    $this->assertGuest();
});

test('admin can log out from customer session and is redirected to home', function (): void {
    Config::set('admin.email', 'testadmin@flavourflow.test');
    Config::set('admin.password', 'SecretPass@123');

    $admin = AdminAccountService::sync();

    $response = $this->actingAs($admin)->post(route('logout'));

    $response->assertRedirect(route('home'));
    $this->assertGuest();
});

test('admin credential sync does not create duplicate admin accounts', function (): void {
    Config::set('admin.email', 'testadmin@flavourflow.test');
    Config::set('admin.password', 'SecretPass@123');

    $admin1 = AdminAccountService::sync();
    $count1 = User::count();

    $admin2 = AdminAccountService::sync();
    $count2 = User::count();

    expect($admin1->id)->toBe($admin2->id);
    expect($count1)->toBe($count2);
});

test('changing configured credentials updates existing admin user without duplicating', function (): void {
    Config::set('admin.email', 'admin_v1@flavourflow.test');
    Config::set('admin.password', 'InitialPassword@123');

    $admin = AdminAccountService::sync();
    $initialId = $admin->id;

    // Change configuration to new credentials
    Config::set('admin.email', 'admin_v2@flavourflow.test');
    Config::set('admin.password', 'NewPassword@456');

    $response = $this->post(route('admin.login'), [
        'email' => 'admin_v2@flavourflow.test',
        'password' => 'NewPassword@456',
    ]);

    $response->assertRedirect(route('admin.index'));
    $this->assertAuthenticated();

    $currentAdmin = auth()->user();
    expect($currentAdmin->id)->toBe($initialId);
    expect($currentAdmin->email)->toBe('admin_v2@flavourflow.test');
    expect(Hash::check('NewPassword@456', $currentAdmin->password))->toBeTrue();
});
