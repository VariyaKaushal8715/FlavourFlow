<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePaymentSettingsRequest;
use App\Services\EnvironmentManager;
use App\Services\RazorpayService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminPaymentSettingController extends Controller
{
    public function index(Request $request, RazorpayService $razorpayService): View
    {
        Gate::authorize('access-admin');

        $currentKeyId = $razorpayService->getKeyId();
        $isDummyKey = $currentKeyId === 'rzp_test_dummy_key_id';
        $displayKeyId = $isDummyKey ? '' : $currentKeyId;

        $isConfigured = $razorpayService->isConfigured();
        $maskedSecret = $razorpayService->getMaskedSecret();
        $mode = $razorpayService->getMode();

        return view('admin.payments.settings', [
            'keyId' => $displayKeyId,
            'hasConfiguredSecret' => ! empty($maskedSecret),
            'maskedSecret' => $maskedSecret,
            'isConfigured' => $isConfigured,
            'mode' => $mode,
        ]);
    }

    public function update(
        UpdatePaymentSettingsRequest $request,
        RazorpayService $razorpayService,
        EnvironmentManager $envManager
    ): RedirectResponse {
        Gate::authorize('access-admin');

        $validated = $request->validated();
        $keyId = $validated['key_id'];

        // If secret was left empty, retain existing configured secret
        $submittedSecret = $validated['key_secret'] ?? null;
        $keySecret = ! empty($submittedSecret) ? $submittedSecret : $razorpayService->getKeySecret();

        if (empty($keySecret) || $keySecret === 'rzp_test_dummy_key_secret') {
            return back()
                ->withInput($request->only('key_id'))
                ->withErrors(['key_secret' => 'Please provide a valid Razorpay Key Secret.']);
        }

        // FAIL-SAFE: Validate credentials against Razorpay API before touching .env
        $validation = $razorpayService->validateCredentials($keyId, $keySecret);

        if (! $validation['valid']) {
            return back()
                ->withInput($request->only('key_id'))
                ->withErrors(['key_id' => $validation['message']]);
        }

        $mode = $validation['mode'] ?? (str_starts_with($keyId, 'rzp_live_') ? 'live' : 'test');

        // Safely update .env entries with atomic writing and automatic backup
        try {
            $envManager->updateEntries([
                'RAZORPAY_KEY_ID' => $keyId,
                'RAZORPAY_KEY_SECRET' => $keySecret,
                'RAZORPAY_MODE' => $mode,
            ]);
        } catch (\Throwable $e) {
            return back()
                ->withInput($request->only('key_id'))
                ->withErrors(['key_id' => 'Failed to save configuration to .env: '.$e->getMessage()]);
        }

        return redirect()
            ->route('admin.payments.settings')
            ->with('status', 'Razorpay credentials have been verified and safely saved to project configuration ('.strtoupper($mode).' mode).');
    }

    public function testConnection(Request $request, RazorpayService $razorpayService): RedirectResponse
    {
        Gate::authorize('access-admin');

        if (! $razorpayService->isConfigured()) {
            return back()->withErrors([
                'connection' => 'Razorpay credentials are not yet configured or are using placeholder test values.',
            ]);
        }

        $validation = $razorpayService->validateCredentials(
            $razorpayService->getKeyId(),
            $razorpayService->getKeySecret()
        );

        if ($validation['valid']) {
            return back()->with(
                'status',
                'Razorpay API Connection Test Successful: '.$validation['message'].' (Mode: '.strtoupper($validation['mode']).')'
            );
        }

        return back()->withErrors([
            'connection' => 'Razorpay API Connection Test Failed: '.$validation['message'],
        ]);
    }
}
