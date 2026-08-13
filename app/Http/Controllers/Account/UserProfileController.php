<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreUserProfileRequest;
use App\Http\Requests\Account\UpdateUserProfileEmailRequest;
use App\Http\Requests\Account\UpdateUserProfileMobileNumberRequest;
use App\Http\Requests\Account\UpdateUserProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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

    public function updateMobileNumber(UpdateUserProfileMobileNumberRequest $request): JsonResponse
    {
        $profile = $request->user()?->profile()->first();

        abort_if($profile === null, 404);

        $profile->update($request->validated());

        return response()->json([
            'message' => 'Your mobile number has been updated.',
            'profile' => [
                'mobile_number' => $profile->mobile_number,
                'email' => $profile->email,
            ],
        ]);
    }

    public function updateEmailAddress(UpdateUserProfileEmailRequest $request): JsonResponse
    {
        $profile = $request->user()?->profile()->first();

        abort_if($profile === null, 404);

        $profile->update($request->validated());

        return response()->json([
            'message' => 'Your email address has been updated.',
            'profile' => [
                'mobile_number' => $profile->mobile_number,
                'email' => $profile->email,
            ],
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->user()?->profile()->delete();

        return redirect()
            ->route('account.profile')
            ->with('status', 'Your profile details have been deleted.');
    }
}
