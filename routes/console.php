<?php

use App\AI\Models\AiEvent;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:sync', function () {
    $email = config('admin.email', env('ADMIN_EMAIL', 'admin@flavourflow.test'));
    $password = config('admin.password', env('ADMIN_PASSWORD', 'FlavourFlow@123'));

    $user = User::query()->updateOrCreate([
        'email' => $email,
    ], [
        'name' => 'FlavourFlow Admin',
        'username' => 'admin',
        'password' => Hash::make($password),
        'is_admin' => true,
    ]);

    $this->info("Admin user synchronized: {$user->email} (ID: {$user->id}, is_admin: 1)");
})->purpose('Sync or create the administrator account from .env credentials');

Artisan::command('admin:make {email}', function (string $email) {
    $user = User::where('email', $email)->orWhere('username', $email)->first();

    if (! $user) {
        $this->error("User not found with email or username: {$email}");

        return 1;
    }

    $user->forceFill(['is_admin' => true])->saveQuietly();
    $this->info("User {$user->email} ({$user->name}) has been granted administrator access!");

    return 0;
})->purpose('Promote a user to administrator by email or username');

Schedule::command('model:prune', ['--model' => [AiEvent::class]])->daily();
