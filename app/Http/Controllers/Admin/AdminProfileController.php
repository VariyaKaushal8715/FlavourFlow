<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminProfileRequest;
use App\Models\AdminProfile;
use App\Models\WhatsAppNotificationLog;
use App\Services\WhatsAppService;
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

    public function sendTestWhatsApp(Request $request): RedirectResponse
    {
        Gate::authorize('access-admin');

        $user = $request->user();
        $profile = $user->adminProfile;
        $mobileNumber = $profile?->mobile_number;

        if (blank($mobileNumber)) {
            return redirect()
                ->route('admin.profile.edit')
                ->with('error', 'Please enter and save a valid Admin Mobile Number before running the WhatsApp test.');
        }

        $waService = app(WhatsAppService::class);
        $testMessage = "🧪 *FlavourFlow WhatsApp Test*\nAdmin: {$user->name}\nTime: ".now()->format('d M Y, h:i A')."\nMeta Cloud API is working correctly!";

        $success = $waService->sendTextMessage(
            recipientPhone: $mobileNumber,
            event: 'admin_test_message',
            message: $testMessage,
            recipientType: 'admin'
        );

        if ($success) {
            $latestLog = WhatsAppNotificationLog::latest()->first();
            $msgIdText = $latestLog?->message_id ? " (Meta Message ID: {$latestLog->message_id})" : ' (Simulated / Logged)';

            return redirect()
                ->route('admin.profile.edit')
                ->with('status', "Test WhatsApp notification dispatched successfully to {$mobileNumber}{$msgIdText}. Check WhatsApp notification log.");
        }

        $latestLog = WhatsAppNotificationLog::latest()->first();
        $errorDetail = isset($latestLog->response_data['error']) ? json_encode($latestLog->response_data['error']) : 'Check logs for details.';

        return redirect()
            ->route('admin.profile.edit')
            ->with('error', "WhatsApp test message failed. Meta API Error: {$errorDetail}");
    }
}
