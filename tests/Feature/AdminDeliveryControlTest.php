<?php

namespace Tests\Feature;

use App\Models\DeliverySetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDeliveryControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_delivery_control(): void
    {
        $response = $this->get(route('admin.delivery.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_user_cannot_access_delivery_control(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get(route('admin.delivery.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_access_delivery_control_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.delivery.index'));
        $response->assertOk();
        $response->assertSee('Delivery Control');
        $response->assertSee('Deliver Everywhere');
        $response->assertSee('Custom Locations');
        $response->assertSee('Apply Changes');
    }

    public function test_admin_can_apply_deliver_everywhere_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->put(route('admin.delivery.update'), [
            'mode' => 'all',
        ]);

        $response->assertRedirect(route('admin.delivery.index'));
        $response->assertSessionHas('status');

        $setting = DeliverySetting::current();
        $this->assertEquals('all', $setting->mode);
        $this->assertTrue($setting->isDeliverable('India', 'Gujarat', 'Surat'));
        $this->assertTrue($setting->isDeliverable('United States'));
    }

    public function test_admin_can_apply_custom_locations_for_india_and_international(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->put(route('admin.delivery.update'), [
            'mode' => 'custom',
            'locations' => [
                [
                    'country' => 'India',
                    'state' => 'Gujarat',
                    'city' => 'Surat',
                ],
                [
                    'country' => 'India',
                    'state' => 'Maharashtra',
                    'city' => 'Mumbai',
                ],
                [
                    'country' => 'United States',
                    'state' => null,
                    'city' => null,
                ],
                [
                    'country' => 'Canada',
                    'state' => null,
                    'city' => null,
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.delivery.index'));
        $response->assertSessionHas('status');

        $setting = DeliverySetting::current();
        $this->assertEquals('custom', $setting->mode);
        $this->assertCount(4, $setting->custom_locations);

        // India matching and non-matching checks
        $this->assertTrue($setting->isDeliverable('India', 'Gujarat', 'Surat'));
        $this->assertTrue($setting->isDeliverable('India', 'Maharashtra', 'Mumbai'));
        $this->assertFalse($setting->isDeliverable('India', 'Gujarat', 'Rajkot'));
        $this->assertFalse($setting->isDeliverable('India', 'Karnataka', 'Bengaluru'));

        // International matching and non-matching checks
        $this->assertTrue($setting->isDeliverable('United States'));
        $this->assertTrue($setting->isDeliverable('USA'));
        $this->assertTrue($setting->isDeliverable('Canada'));
        $this->assertFalse($setting->isDeliverable('United Kingdom'));
        $this->assertFalse($setting->isDeliverable('Australia'));
    }

    public function test_settings_persist_in_database_only_when_applied(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Start with Deliver Everywhere
        DeliverySetting::create([
            'mode' => 'all',
            'custom_locations' => [],
            'is_active' => true,
        ]);

        // Check active database setting before any update request
        $this->assertEquals('all', DeliverySetting::current()->mode);

        // Submit Apply Changes
        $this->actingAs($admin)->put(route('admin.delivery.update'), [
            'mode' => 'custom',
            'locations' => [
                ['country' => 'India', 'state' => 'Gujarat', 'city' => 'Ahmedabad'],
            ],
        ]);

        // Now updated in database
        $this->assertEquals('custom', DeliverySetting::current()->mode);
        $this->assertTrue(DeliverySetting::current()->isDeliverable('India', 'Gujarat', 'Ahmedabad'));
        $this->assertFalse(DeliverySetting::current()->isDeliverable('India', 'Gujarat', 'Surat'));
    }
}
