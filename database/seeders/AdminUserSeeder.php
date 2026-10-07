<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the administrator account from environment configuration.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => config('admin.email', env('ADMIN_EMAIL', 'admin@flavourflow.test')),
        ], [
            'name' => 'FlavourFlow Admin',
            'password' => config('admin.password', env('ADMIN_PASSWORD', 'FlavourFlow@123')),
            'is_admin' => true,
        ]);
    }
}
