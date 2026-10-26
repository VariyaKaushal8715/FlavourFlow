<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.entry', [
            'site' => config('personal_site'),
            'activeForm' => 'register',
        ]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $adminEmail = config('admin.email', env('ADMIN_EMAIL'));
        $email = $request->string('email')->trim()->toString();
        $isAdmin = ! empty($adminEmail) && strcasecmp($email, $adminEmail) === 0;

        $user = User::create([
            'name' => $request->string('full_name')->toString(),
            'username' => $request->string('username')->toString(),
            'email' => $email,
            'password' => Hash::make($request->string('password')->toString()),
            'is_admin' => $isAdmin,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        if ($isAdmin) {
            $request->session()->forget('url.intended');

            return redirect()->route('admin.index')->with('success', 'Administrator account created.');
        }

        return redirect()->route('home')->with('success', 'Your account has been created.');
    }
}
