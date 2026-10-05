<?php

namespace App\Http\Controllers;

use App\Models\PrivacyConsent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PrivacyConsentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'consent_status' => ['required', 'string', 'in:allowed,declined'],
        ]);

        $this->saveConsent($request, $validated['consent_status']);

        return back()->with('status', 'Your privacy preference has been saved.');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'consent_status' => ['required', 'string', 'in:allowed,declined'],
        ]);

        $this->saveConsent($request, $validated['consent_status']);

        return back()->with('status', 'Your privacy preference has been updated.');
    }

    private function saveConsent(Request $request, string $status): void
    {
        PrivacyConsent::query()->updateOrCreate(
            ['user_id' => $request->user()->getKey()],
            [
                'consent_status' => $status,
                'activity_tracking' => $status === PrivacyConsent::STATUS_ALLOWED,
                'consent_version' => PrivacyConsent::VERSION,
                'consented_at' => now(),
            ],
        );
    }
}
