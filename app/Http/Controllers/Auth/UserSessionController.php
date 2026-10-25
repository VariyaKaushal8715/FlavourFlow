<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AdminAccountService;
use App\Support\CartState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.entry', [
            'site' => config('personal_site'),
            'activeForm' => 'login',
        ]);
    }

    public function store(LoginRequest $request, CartState $cart): RedirectResponse
    {
        $adminEmail = config('admin.email');
        $adminUsername = config('admin.username', 'admin');
        $loginInput = trim((string) $request->input('login'));

        if ($adminEmail && (strcasecmp($loginInput, $adminEmail) === 0 || strcasecmp($loginInput, $adminUsername) === 0)) {
            AdminAccountService::sync();
        }

        if (! Auth::attempt($request->credentials(), $request->boolean('remember'))) {
            return back()
                ->withErrors(['login' => 'These credentials do not match our records.'])
                ->onlyInput('login');
        }

        $request->session()->regenerate();

        if (Auth::user()) {
            $cart->migrateGuestCartToUser(Auth::user());
        }

        if (Auth::user()?->is_admin) {
            return redirect()->intended(route('admin.index'));
        }

        return redirect()->intended(route('home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
