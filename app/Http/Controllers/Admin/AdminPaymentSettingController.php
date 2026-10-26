<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePaymentSettingsRequest;
use App\Models\PaymentAuditLog;
use App\Models\PaymentGatewaySetting;
use App\Services\CashfreeService;
use App\Services\EnvironmentManager;
use App\Services\RazorpayService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AdminPaymentSettingController extends Controller
{
    public function index(
        Request $request,
        RazorpayService $razorpayService,
        CashfreeService $cashfreeService
    ): View {
        Gate::authorize('access-admin');

        $setting = PaymentGatewaySetting::current();

        $currentKeyId = $setting->getEffectiveRazorpayKeyId();
        $isDummyKey = $currentKeyId === 'rzp_test_dummy_key_id';
        $displayKeyId = $isDummyKey ? '' : $currentKeyId;

        $isConfigured = $razorpayService->isConfigured();
        $maskedSecret = $razorpayService->getMaskedSecret();
        $mode = $razorpayService->getMode();

        // Cashfree status & credentials
        $cfAppId = $setting->getEffectiveCashfreeAppId();
        $displayCfAppId = $cfAppId;
        $isCfConfigured = $cashfreeService->isConfigured();
        $cfMaskedSecret = $cashfreeService->getMaskedSecret();
        $cfMode = $cashfreeService->isSandbox() ? 'sandbox' : 'production';

        // Security Audit Logs (Sanitized, no secrets)
        $auditLogs = PaymentAuditLog::with('user')
            ->latest()
            ->take(15)
            ->get();

        return view('admin.payments.settings', [
            'setting' => $setting,
            'activeGateway' => $setting->active_gateway,
            'environment' => $setting->environment,

            // Razorpay
            'keyId' => $displayKeyId,
            'hasConfiguredSecret' => ! empty($maskedSecret),
            'maskedSecret' => $maskedSecret,
            'hasConfiguredWebhookSecret' => ! empty($razorpayService->getMaskedWebhookSecret()),
            'maskedWebhookSecret' => $razorpayService->getMaskedWebhookSecret(),
            'isConfigured' => $isConfigured,
            'mode' => $mode,
            'razorpayWebhookUrl' => url('/api/webhooks/razorpay'),

            // Cashfree
            'cfAppId' => $displayCfAppId,
            'hasConfiguredCfSecret' => ! empty($cfMaskedSecret),
            'cfMaskedSecret' => $cfMaskedSecret,
            'isCfConfigured' => $isCfConfigured,
            'cfMode' => $cfMode,
            'cashfreeWebhookUrl' => url('/api/webhooks/cashfree'),

            // Audit Logs
            'auditLogs' => $auditLogs,
        ]);
    }

    /**
     * Update active payment gateway and environment mode (Demo / Cashfree / Razorpay).
     */
    public function updateGateway(Request $request): RedirectResponse
    {
        Gate::authorize('access-admin');

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'active_gateway' => ['required', 'string', 'in:demo,cashfree,razorpay'],
            'environment' => ['required', 'string', 'in:sandbox,live'],
        ], [
            'current_password.required' => 'Please re-enter your administrator password to confirm gateway changes.',
            'active_gateway.in' => 'Please select a valid payment gateway option.',
            'environment.in' => 'Please select a valid environment mode.',
        ]);

        // Security Check 1: Rate Limiter on Administrator Password Verification
        $throttleKey = 'admin-payment-auth:'.$request->user()->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'current_password' => "Too many authentication attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        // Security Check 2: Verify current administrator password
        if (! Hash::check($validated['current_password'], $request->user()->password)) {
            RateLimiter::hit($throttleKey, 60);

            PaymentAuditLog::logAction('failed_password_attempt', $request->user(), $validated['active_gateway'], $validated['environment'], [
                'target' => 'update_gateway_mode',
            ]);

            return back()->withErrors([
                'current_password' => 'The provided administrator password does not match your account.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        $setting = PaymentGatewaySetting::current();
        $targetGateway = $validated['active_gateway'];
        $targetEnv = $validated['environment'];

        // Security Check 3: Live Mode Safeguard
        if ($targetEnv === 'live') {
            if ($targetGateway === 'demo') {
                return back()->withErrors([
                    'environment' => 'Demo Payment Mode is simulated and cannot be run in Live Production.',
                ]);
            }

            if (! $setting->canActivateLive($targetGateway)) {
                return back()->withErrors([
                    'environment' => 'Cannot activate Live Production mode without configured live credentials. Live payments require your own verified merchant gateway account.',
                ]);
            }
        }

        $oldGateway = $setting->active_gateway;
        $oldEnv = $setting->environment;

        $setting->update([
            'active_gateway' => $targetGateway,
            'environment' => $targetEnv,
        ]);

        PaymentAuditLog::logAction('gateway_mode_updated', $request->user(), $targetGateway, $targetEnv, [
            'previous_gateway' => $oldGateway,
            'previous_environment' => $oldEnv,
            'new_gateway' => $targetGateway,
            'new_environment' => $targetEnv,
        ]);

        $gatewayName = match ($targetGateway) {
            'demo' => 'Zero-Credential Demo Mode',
            'cashfree' => 'Cashfree Payments',
            'razorpay' => 'Razorpay Gateway',
            default => strtoupper($targetGateway),
        };

        return redirect()
            ->route('admin.payments.settings')
            ->with('status', "Payment settings updated: Active gateway set to {$gatewayName} in ".strtoupper($targetEnv).' mode.');
    }

    public function update(
        UpdatePaymentSettingsRequest $request,
        RazorpayService $razorpayService,
        EnvironmentManager $envManager
    ): RedirectResponse {
        Gate::authorize('access-admin');

        $validated = $request->validated();

        // Verify current password with RateLimiter
        $throttleKey = 'admin-payment-auth:'.$request->user()->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('key_id'))
                ->withErrors(['current_password' => "Too many attempts. Please try again in {$seconds} seconds."]);
        }

        if (! Hash::check($validated['current_password'], $request->user()->password)) {
            RateLimiter::hit($throttleKey, 60);

            PaymentAuditLog::logAction('failed_password_attempt', $request->user(), 'razorpay', null, [
                'target' => 'update_razorpay_credentials',
            ]);

            return back()
                ->withInput($request->only('key_id'))
                ->withErrors(['current_password' => 'The provided administrator password does not match your account.']);
        }

        RateLimiter::clear($throttleKey);

        $keyId = $validated['key_id'];
        $setting = PaymentGatewaySetting::current();

        // If secret was left empty, retain existing configured secret
        $submittedSecret = $validated['key_secret'] ?? null;
        $keySecret = ! empty($submittedSecret) ? $submittedSecret : $setting->getEffectiveRazorpayKeySecret();

        if (empty($keySecret) || $keySecret === 'rzp_test_dummy_key_secret') {
            return back()
                ->withInput($request->only('key_id'))
                ->withErrors(['key_secret' => 'Please provide a valid Razorpay Key Secret.']);
        }

        // FAIL-SAFE: Validate credentials against Razorpay API before saving
        $validation = $razorpayService->validateCredentials($keyId, $keySecret);

        if (! $validation['valid']) {
            return back()
                ->withInput($request->only('key_id'))
                ->withErrors(['key_id' => $validation['message']]);
        }

        $mode = $validation['mode'] ?? (str_starts_with($keyId, 'rzp_live_') ? 'live' : 'test');

        $submittedWebhookSecret = $validated['webhook_secret'] ?? null;
        $webhookSecret = ! empty($submittedWebhookSecret) ? $submittedWebhookSecret : $setting->getEffectiveRazorpayWebhookSecret();

        // Store credentials securely using server-side encryption in database
        $updateData = [
            'razorpay_key_id' => $keyId,
            'razorpay_key_secret' => $keySecret,
        ];

        if (! empty($webhookSecret)) {
            $updateData['razorpay_webhook_secret'] = $webhookSecret;
        }

        $setting->update($updateData);

        // Safely synchronize runtime configuration
        try {
            $envEntries = [
                'RAZORPAY_KEY_ID' => $keyId,
                'RAZORPAY_KEY_SECRET' => $keySecret,
                'RAZORPAY_MODE' => $mode,
            ];
            if (! empty($webhookSecret)) {
                $envEntries['RAZORPAY_WEBHOOK_SECRET'] = $webhookSecret;
            }
            $envManager->updateEntries($envEntries);
        } catch (\Throwable) {
            // Server-side database encrypted store remains primary
        }

        PaymentAuditLog::logAction('razorpay_credentials_updated', $request->user(), 'razorpay', $mode, [
            'key_id' => $keyId,
            'mode' => $mode,
            'has_webhook_secret' => ! empty($webhookSecret),
        ]);

        return redirect()
            ->route('admin.payments.settings')
            ->with('status', 'Razorpay credentials have been verified and securely saved ('.strtoupper($mode).' mode).');
    }

    public function updateCashfree(
        Request $request,
        CashfreeService $cashfreeService,
        EnvironmentManager $envManager
    ): RedirectResponse {
        Gate::authorize('access-admin');

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'cashfree_app_id' => ['required', 'string', 'max:255'],
            'cashfree_secret_key' => [
                $cashfreeService->isConfigured() ? 'nullable' : 'required',
                'string',
                'min:8',
            ],
        ], [
            'current_password.required' => 'Please re-enter your administrator password to confirm credential changes.',
        ]);

        // Security Check: Rate Limiter on Administrator Password
        $throttleKey = 'admin-payment-auth:'.$request->user()->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('cashfree_app_id'))
                ->withErrors(['current_password' => "Too many attempts. Please try again in {$seconds} seconds."]);
        }

        if (! Hash::check($validated['current_password'], $request->user()->password)) {
            RateLimiter::hit($throttleKey, 60);

            PaymentAuditLog::logAction('failed_password_attempt', $request->user(), 'cashfree', null, [
                'target' => 'update_cashfree_credentials',
            ]);

            return back()
                ->withInput($request->only('cashfree_app_id'))
                ->withErrors(['current_password' => 'The provided administrator password does not match your account.']);
        }

        RateLimiter::clear($throttleKey);

        $appId = trim($validated['cashfree_app_id']);
        $setting = PaymentGatewaySetting::current();
        $submittedSecret = $validated['cashfree_secret_key'] ?? null;
        $secretKey = ! empty($submittedSecret) ? trim($submittedSecret) : $setting->getEffectiveCashfreeSecretKey();

        if (empty($secretKey)) {
            return back()
                ->withInput($request->only('cashfree_app_id'))
                ->withErrors(['cashfree_secret_key' => 'Please provide a valid Cashfree Secret Key.']);
        }

        // Safe Credential validation against Cashfree API
        $env = $setting->isLive() ? 'PRODUCTION' : 'TEST';
        $validation = $cashfreeService->validateCredentials($appId, $secretKey, $env);

        if (! $validation['valid']) {
            return back()
                ->withInput($request->only('cashfree_app_id'))
                ->withErrors(['cashfree_app_id' => $validation['message']]);
        }

        // Store credentials securely using server-side encryption in database
        $setting->update([
            'cashfree_app_id' => $appId,
            'cashfree_secret_key' => $secretKey,
        ]);

        // Safely synchronize runtime configuration
        try {
            $envManager->updateEntries([
                'CASHFREE_APP_ID' => $appId,
                'CASHFREE_SECRET_KEY' => $secretKey,
                'CASHFREE_ENV' => $env,
                'CASHFREE_API_VERSION' => '2023-08-01',
            ]);
        } catch (\Throwable) {
            // Database encrypted store remains primary
        }

        PaymentAuditLog::logAction('cashfree_credentials_updated', $request->user(), 'cashfree', $env, [
            'app_id' => $appId,
            'environment' => $env,
        ]);

        return redirect()
            ->route('admin.payments.settings')
            ->with('status', 'Cashfree credentials verified and securely saved successfully ('.strtoupper($env).' mode).');
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

        PaymentAuditLog::logAction('tested_razorpay_connection', $request->user(), 'razorpay', $validation['mode'] ?? 'test', [
            'success' => $validation['valid'],
            'message' => $validation['message'],
        ]);

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

    public function testCashfreeConnection(Request $request, CashfreeService $cashfreeService): RedirectResponse
    {
        Gate::authorize('access-admin');

        if (! $cashfreeService->isConfigured()) {
            return back()->withErrors([
                'connection' => 'Cashfree credentials are not yet configured.',
            ]);
        }

        $validation = $cashfreeService->validateCredentials(
            $cashfreeService->getAppId(),
            $cashfreeService->getSecretKey(),
            $cashfreeService->getEnvironment()
        );

        PaymentAuditLog::logAction('tested_cashfree_connection', $request->user(), 'cashfree', $cashfreeService->getEnvironment(), [
            'success' => $validation['valid'],
            'message' => $validation['message'],
        ]);

        if ($validation['valid']) {
            return back()->with(
                'status',
                'Cashfree API Connection Test Successful: '.$validation['message'].' (Mode: '.strtoupper($validation['mode']).')'
            );
        }

        return back()->withErrors([
            'connection' => 'Cashfree API Connection Test Failed: '.$validation['message'],
        ]);
    }
}
