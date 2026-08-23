<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSessionController extends Controller
{
    public function store(AdminLoginRequest $request): RedirectResponse
    {
        $credentials = [
            ...$request->safe()->only(['email', 'password']),
            'is_admin' => true,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'These credentials do not match an administrator account.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

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
