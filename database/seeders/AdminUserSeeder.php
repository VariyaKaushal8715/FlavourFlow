<?php

namespace Database\Seeders;

use App\Services\AdminAccountService;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the administrator account from environment configuration.
     */
    public function run(): void
    {
        AdminAccountService::sync();
    }
}
