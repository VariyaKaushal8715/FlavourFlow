<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreUserProfileRequest;
use App\Http\Requests\Account\UpdateUserProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('account.profile', [
            'site' => config('personal_site'),
            'profile' => $user?->profile()->first(),
        ]);
    }

    public function store(StoreUserProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user?->profile()->first();

        abort_unless($user !== null, 403);
        abort_if($profile !== null, 409);

        $user->profile()->create($request->validated());

        return redirect()
            ->route('account.profile')
            ->with('status', 'Your profile details have been saved.');
    }

    public function update(UpdateUserProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user?->profile()->first();

        abort_if($profile === null, 404);

        $profile->update($request->validated());

        return redirect()
            ->route('account.profile')
            ->with('status', 'Your profile details have been updated.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->user()?->profile()->delete();

        return redirect()
            ->route('account.profile')
            ->with('status', 'Your profile details have been deleted.');
    }
}
