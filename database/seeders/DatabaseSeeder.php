<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\CouponRewardRule;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => config('admin.email'),
        ], [
            'name' => 'FlavourFlow Admin',
            'password' => config('admin.password'),
            'is_admin' => true,
        ]);

        $this->call([
            ProductSeeder::class,
            OfferSeeder::class,
        ]);

        CouponRewardRule::query()->updateOrCreate([
            'name' => '10% Online Payment Reward (₹1,000+)',
        ], [
            'min_purchase_amount' => 1000.00,
            'discount_type' => 'percent',
            'discount_value' => 10.00,
            'max_discount' => 300.00,
            'min_next_order_amount' => 500.00,
            'validity_days' => 30,
            'is_active' => true,
        ]);

        CouponRewardRule::query()->updateOrCreate([
            'name' => '15% Online Payment Reward (₹2,000+)',
        ], [
            'min_purchase_amount' => 2000.00,
            'discount_type' => 'percent',
            'discount_value' => 15.00,
            'max_discount' => 500.00,
            'min_next_order_amount' => 800.00,
            'validity_days' => 30,
            'is_active' => true,
        ]);

        Coupon::query()->updateOrCreate([
            'code' => 'SPICE10',
        ], [
            'title' => '10% Welcome Discount',
            'description' => 'Get 10% off on all spices above Rs. 100',
            'discount_type' => 'percent',
            'discount_value' => 10.00,
            'is_active' => true,
            'min_order_amount' => 100.00,
            'max_discount' => 150.00,
            'usage_limit' => 100,
            'expires_at' => now()->addMonths(3),
        ]);

        Coupon::query()->updateOrCreate([
            'code' => 'SAVE50',
        ], [
            'title' => 'Flat Rs. 50 Discount',
            'description' => 'Save flat Rs. 50 on orders above Rs. 200',
            'discount_type' => 'fixed',
            'discount_value' => 50.00,
            'is_active' => true,
            'min_order_amount' => 200.00,
            'usage_limit' => 50,
            'expires_at' => now()->addMonths(3),
        ]);
    }
}
