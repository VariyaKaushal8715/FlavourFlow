<?php

use App\Models\PaymentAuditLog;
use App\Models\PaymentGatewaySetting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('guests are redirected when attempting to access payment settings', function () {
    $response = $this->get(route('admin.payments.settings'));

    $response->assertRedirect(route('login'));
});

test('non-admin authenticated users are forbidden from viewing payment settings', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get(route('admin.payments.settings'));

    $response->assertForbidden();
});

test('non-admin authenticated users are forbidden from updating UPI settings', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->put(route('admin.payments.settings.upi.update'), [
        'upi_id' => 'hacker@upi',
        'upi_name' => 'Hacker Store',
        'current_password' => 'password',
    ]);

    $response->assertForbidden();
});

test('authenticated admin can view UPI settings section', function () {
    $admin = User::factory()->admin()->create();

    $setting = PaymentGatewaySetting::current();
    $setting->update([
        'upi_id' => 'flavourflow@okhdfcbank',
        'upi_name' => 'FlavourFlow Spices',
        'upi_note' => 'Spices Order Payment',
        'upi_enabled' => true,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.payments.settings'));

    $response->assertSuccessful();
    $response->assertSee('UPI QR Code Settings (Manual Confirmation)');
    $response->assertSee('flavourflow@okhdfcbank');
    $response->assertSee('FlavourFlow Spices');
    $response->assertSee('ACTIVE &amp; CONFIGURED', false);
});

test('admin cannot update UPI settings without current password confirmation', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put(route('admin.payments.settings.upi.update'), [
        'upi_id' => 'store@okhdfcbank',
        'upi_name' => 'Store Name',
        'current_password' => '',
    ]);

    $response->assertSessionHasErrors(['current_password']);
});

test('admin cannot update UPI settings with wrong password', function () {
    $admin = User::factory()->admin()->create([
        'password' => bcrypt('correct-password'),
    ]);

    $response = $this->actingAs($admin)->put(route('admin.payments.settings.upi.update'), [
        'upi_id' => 'store@okhdfcbank',
        'upi_name' => 'Store Name',
        'current_password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors(['current_password']);
});

test('admin cannot save invalid UPI ID format', function () {
    $admin = User::factory()->admin()->create([
        'password' => bcrypt('admin-password'),
    ]);

    $response = $this->actingAs($admin)->put(route('admin.payments.settings.upi.update'), [
        'upi_id' => 'invalid_without_at_symbol',
        'upi_name' => 'Store Name',
        'current_password' => 'admin-password',
    ]);

    $response->assertSessionHasErrors(['upi_id']);
});

test('admin can successfully update UPI settings and audit log is recorded', function () {
    $admin = User::factory()->admin()->create([
        'password' => bcrypt('admin-password'),
    ]);

    $response = $this->actingAs($admin)->put(route('admin.payments.settings.upi.update'), [
        'upi_id' => 'payments.store@icici',
        'upi_name' => 'FlavourFlow Flagship',
        'upi_note' => 'Official Spice Order',
        'upi_enabled' => '1',
        'current_password' => 'admin-password',
    ]);

    $response->assertRedirect(route('admin.payments.settings'));
    $response->assertSessionHas('status');

    $setting = PaymentGatewaySetting::current();
    expect($setting->upi_id)->toBe('payments.store@icici')
        ->and($setting->upi_name)->toBe('FlavourFlow Flagship')
        ->and($setting->upi_note)->toBe('Official Spice Order')
        ->and($setting->isUpiEnabled())->toBeTrue();

    // Verify audit log
    $log = PaymentAuditLog::where('action', 'upi_settings_updated')->latest()->first();
    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($admin->id)
        ->and($log->gateway)->toBe('upi')
        ->and($log->details['upi_id'])->toBe('payments.store@icici');
});
