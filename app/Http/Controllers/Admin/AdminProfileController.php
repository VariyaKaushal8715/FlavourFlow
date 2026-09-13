<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminProfileRequest;
use App\Models\AdminProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminProfileController extends Controller
{
    public function edit(Request $request): View
    {
        Gate::authorize('access-admin');

        $user = $request->user();
        $profile = $user->adminProfile()->first() ?: new AdminProfile([
            'user_id' => $user->id,
            'full_name' => $user->name,
            'email' => $user->email,
            'country' => 'India',
        ]);

        return view('admin.profile.index', compact('user', 'profile'));
    }

    public function update(UpdateAdminProfileRequest $request): RedirectResponse
    {
        Gate::authorize('access-admin');

        $user = $request->user();
        $validated = $request->validated();

        $profile = AdminProfile::firstOrNew(['user_id' => $user->id]);
        $profile->fill($validated);
        $profile->user_id = $user->id;
        $profile->save();

        // Keep User auth credentials synchronized with profile updates
        $user->forceFill([
            'name' => $validated['full_name'],
            'email' => $validated['email'],
        ])->save();

        $user->unsetRelation('adminProfile');

        return redirect()
            ->route('admin.profile.edit')
            ->with('status', 'Your admin profile details have been updated successfully.');
    }
}
