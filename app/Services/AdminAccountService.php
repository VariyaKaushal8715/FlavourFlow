<?php

namespace App\Services;

use App\Models\AdminProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminAccountService
{
    /**
     * Synchronize the administrator account with credentials from configuration.
     */
    public static function sync(): ?User
    {
        $adminEmail = config('admin.email');
        $adminPassword = config('admin.password');
        $adminUsername = config('admin.username', 'admin');

        if (! $adminEmail || ! $adminPassword) {
            return null;
        }

        // Prioritize finding the existing admin account to prevent duplicate accounts
        $admin = User::query()->where('is_admin', true)->whereHas('adminProfile')->first()
            ?? User::query()->where('is_admin', true)->orderBy('id')->first()
            ?? User::query()->where('email', $adminEmail)->first()
            ?? User::query()->where('username', $adminUsername)->first()
            ?? new User;

        $admin->name = $admin->name ?: 'FlavourFlow Admin';
        $admin->username = $admin->username ?: $adminUsername;
        $admin->email = $adminEmail;
        $admin->is_admin = true;

        if (! $admin->exists || ! Hash::check($adminPassword, $admin->password)) {
            $admin->password = $adminPassword;
        }

        $admin->save();

        if ($admin->id) {
            $profile = AdminProfile::firstOrNew(['user_id' => $admin->id]);
            if (! $profile->exists) {
                $profile->full_name = $admin->name;
                $profile->email = $adminEmail;
                $profile->save();
            }
        }

        return $admin;
    }
}
