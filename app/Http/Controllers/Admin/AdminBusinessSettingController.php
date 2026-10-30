<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AdminBusinessSettingController extends Controller
{
    /**
     * Display the Business Details and Invoice configuration screen.
     */
    public function edit(): View
    {
        Gate::authorize('access-admin');

        $setting = BusinessSetting::current();

        return view('admin.business.settings', [
            'setting' => $setting,
        ]);
    }

    /**
     * Update business details, invoice prefixes, and branding.
     */
    public function update(Request $request): RedirectResponse
    {
        Gate::authorize('access-admin');

        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:150'],
            'legal_name' => ['nullable', 'string', 'max:200'],
            'logo' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'business_address' => ['required', 'string', 'max:1000'],
            'billing_address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:150'],
            'website_url' => ['nullable', 'url', 'max:200'],
            'gstin' => ['nullable', 'string', 'max:30', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i'],
            'invoice_prefix' => ['required', 'string', 'min:2', 'max:10', 'regex:/^[a-zA-Z0-9_\-]+$/'],
            'next_invoice_number' => ['required', 'integer', 'min:1', 'max:999999999'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_label' => ['nullable', 'string', 'max:50'],
            'tax_mode' => ['required', 'string', 'in:inclusive,exclusive,none'],
            'invoice_footer' => ['nullable', 'string', 'max:1000'],
            'payment_instructions' => ['nullable', 'string', 'max:1000'],
            'return_policy_text' => ['nullable', 'string', 'max:1000'],
        ], [
            'gstin.regex' => 'Please enter a valid 15-character Indian GSTIN format (e.g. 24ABCDE1234F1Z5).',
            'invoice_prefix.regex' => 'Invoice prefix may only contain alphanumeric characters, hyphens, and underscores.',
            'logo.max' => 'Logo file size must not exceed 2MB.',
            'logo.mimes' => 'Logo must be an image of type: PNG, JPG, JPEG, or WEBP.',
        ]);

        $setting = BusinessSetting::current();

        // Secure file upload handling
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            if ($setting->hasCustomLogo()) {
                Storage::disk('public')->delete($setting->logo_path);
            }

            $validated['logo_path'] = $request->file('logo')->store('business', 'public');
        }

        // Format and clean values
        $validated['invoice_prefix'] = strtoupper(trim($validated['invoice_prefix']));
        if (! empty($validated['gstin'])) {
            $validated['gstin'] = strtoupper(trim($validated['gstin']));
        }
        $validated['tax_rate'] = (float) ($validated['tax_rate'] ?? 0.00);

        unset($validated['logo']);

        $setting->update($validated);

        return redirect()->route('admin.business.settings')
            ->with('status', 'Business details and invoice settings updated successfully.');
    }

    /**
     * Remove custom uploaded store logo and reset to default.
     */
    public function destroyLogo(): RedirectResponse
    {
        Gate::authorize('access-admin');

        $setting = BusinessSetting::current();

        if ($setting->hasCustomLogo()) {
            Storage::disk('public')->delete($setting->logo_path);
        }

        $setting->update([
            'logo_path' => null,
        ]);

        return redirect()->route('admin.business.settings')
            ->with('status', 'Store logo reset to default brand mark successfully.');
    }
}
