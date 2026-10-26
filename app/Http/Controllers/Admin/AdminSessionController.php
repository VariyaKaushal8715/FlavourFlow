<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminSessionController extends Controller
{
    public function store(AdminLoginRequest $request): RedirectResponse
    {
        $email = $request->string('email')->trim()->toString();
        $password = $request->string('password')->toString();
        $remember = $request->boolean('remember');

        $configuredEmail = config('admin.email', env('ADMIN_EMAIL'));
        $configuredPassword = config('admin.password', env('ADMIN_PASSWORD'));

        // If credentials match the configured .env admin credentials, ensure admin user exists and is flagged as admin
        if (! empty($configuredEmail) && strcasecmp($email, $configuredEmail) === 0 && ! empty($configuredPassword) && $password === $configuredPassword) {
            $adminUser = User::query()->updateOrCreate([
                'email' => $configuredEmail,
            ], [
                'name' => 'FlavourFlow Admin',
                'username' => 'admin',
                'password' => Hash::make($configuredPassword),
                'is_admin' => true,
            ]);

            Auth::login($adminUser, $remember);
            $request->session()->regenerate();
            $request->session()->forget('url.intended');

            return redirect()->route('admin.index');
        }

        // If user exists with matching password and matches the configured admin email, ensure is_admin is true
        if (! empty($configuredEmail) && strcasecmp($email, $configuredEmail) === 0) {
            $existingUser = User::where('email', $email)->first();
            if ($existingUser && Hash::check($password, $existingUser->password)) {
                $existingUser->forceFill(['is_admin' => true])->saveQuietly();
            }
        }

        $credentials = [
            'email' => $email,
            'password' => $password,
            'is_admin' => true,
        ];

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'These credentials do not match an administrator account.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return redirect()->route('admin.index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.index');
    }
}
