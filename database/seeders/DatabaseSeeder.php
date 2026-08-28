<?php

namespace Database\Seeders;

use App\Models\Coupon;
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

        Coupon::query()->updateOrCreate([
            'code' => 'SPICE10',
        ], [
            'type' => 'percent',
            'value' => 10.00,
            'is_active' => true,
            'min_order_amount' => 100.00,
            'max_discount' => 150.00,
            'usage_limit' => 100,
        ]);

        Coupon::query()->updateOrCreate([
            'code' => 'SAVE50',
        ], [
            'type' => 'fixed',
            'value' => 50.00,
            'is_active' => true,
            'min_order_amount' => 200.00,
            'usage_limit' => 50,
        ]);
    }
}
