<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliverySetting;
use App\Support\LocationData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminDeliveryController extends Controller
{
    /**
     * Display the delivery control dashboard.
     */
    public function index(): View
    {
        $setting = DeliverySetting::current();

        return view('admin.delivery.index', [
            'setting' => $setting,
            'countries' => LocationData::getCountries(),
            'indiaStatesWithCities' => LocationData::getIndiaStatesWithCities(),
        ]);
    }

    /**
     * Save and apply delivery settings.
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:all,custom'],
            'locations' => ['nullable', 'array'],
            'locations.*.country' => ['required_with:locations', 'string', 'max:100'],
            'locations.*.state' => ['nullable', 'string', 'max:100'],
            'locations.*.city' => ['nullable', 'string', 'max:100'],
        ]);

        $mode = $validated['mode'];
        $locations = [];

        if ($mode === 'custom' && ! empty($validated['locations'])) {
            foreach ($validated['locations'] as $loc) {
                $country = trim($loc['country'] ?? '');
                if (empty($country)) {
                    continue;
                }

                $isIndia = DeliverySetting::normalizeCountry($country) === 'india';
                $state = $isIndia ? trim($loc['state'] ?? '') : null;
                $city = $isIndia ? trim($loc['city'] ?? '') : null;

                $locations[] = [
                    'country' => $country,
                    'state' => $state ?: null,
                    'city' => $city ?: null,
                ];
            }
        }

        $setting = DeliverySetting::current();
        $setting->update([
            'mode' => $mode,
            'custom_locations' => $locations,
            'is_active' => true,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Changes successfully applied.',
                'setting' => [
                    'mode' => $setting->mode,
                    'custom_locations_count' => count($setting->custom_locations ?? []),
                ],
            ]);
        }

        return redirect()->route('admin.delivery.index')
            ->with('status', 'Changes successfully applied.');
    }
}
