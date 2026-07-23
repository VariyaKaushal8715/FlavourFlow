<?php

namespace Database\Seeders;

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

        $this->call(ProductSeeder::class);
    }
}
