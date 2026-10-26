<?php

namespace App\Http\Controllers;

use App\AI\Services\CustomerPersonalizationService;
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
        $userId = (int) $request->user()->getKey();

        PrivacyConsent::query()->updateOrCreate(
            ['user_id' => $userId],
            [
                'consent_status' => $status,
                'activity_tracking' => $status === PrivacyConsent::STATUS_ALLOWED,
                'consent_version' => PrivacyConsent::VERSION,
                'consented_at' => now(),
            ],
        );

        if ($status === PrivacyConsent::STATUS_DECLINED) {
            app(CustomerPersonalizationService::class)->forgetProfile($userId);
        }
    }
}
