<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliverySetting;
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

        $countries = [
            'India',
            'United States',
            'Canada',
            'United Kingdom',
            'Australia',
            'Germany',
            'France',
            'United Arab Emirates',
            'Singapore',
            'Japan',
            'New Zealand',
            'Netherlands',
            'Saudi Arabia',
            'Switzerland',
            'Italy',
            'Spain',
        ];

        $indiaStatesWithCities = [
            'Gujarat' => ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar', 'Gandhinagar', 'Junagadh', 'Anand', 'Navsari', 'Bharuch', 'Vapi'],
            'Maharashtra' => ['Mumbai', 'Pune', 'Nagpur', 'Nashik', 'Thane', 'Aurangabad', 'Solapur', 'Navi Mumbai', 'Kolhapur', 'Amravati'],
            'Delhi' => ['New Delhi', 'Central Delhi', 'South Delhi', 'North Delhi', 'East Delhi', 'West Delhi', 'Dwarka', 'Rohini'],
            'Karnataka' => ['Bengaluru', 'Mysuru', 'Mangaluru', 'Hubballi', 'Belagavi', 'Davangere', 'Ballari'],
            'Tamil Nadu' => ['Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli', 'Salem', 'Tirunelveli', 'Erode'],
            'Uttar Pradesh' => ['Lucknow', 'Kanpur', 'Varanasi', 'Agra', 'Noida', 'Ghaziabad', 'Prayagraj', 'Meerut', 'Bareilly', 'Aligarh'],
            'Rajasthan' => ['Jaipur', 'Jodhpur', 'Udaipur', 'Kota', 'Bikaner', 'Ajmer', 'Bhilwara', 'Alwar'],
            'Punjab' => ['Amritsar', 'Ludhiana', 'Jalandhar', 'Patiala', 'Bathinda', 'Mohali'],
            'Telangana' => ['Hyderabad', 'Warangal', 'Nizamabad', 'Karimnagar', 'Ramagundam', 'Khammam'],
            'West Bengal' => ['Kolkata', 'Howrah', 'Siliguri', 'Durgapur', 'Asansol', 'Bardhaman'],
            'Kerala' => ['Thiruvananthapuram', 'Kochi', 'Kozhikode', 'Thrissur', 'Kollam', 'Kannur', 'Alappuzha'],
            'Madhya Pradesh' => ['Bhopal', 'Indore', 'Jabalpur', 'Gwalior', 'Ujjain', 'Sagar'],
            'Haryana' => ['Gurugram', 'Faridabad', 'Panipat', 'Ambala', 'Karnal', 'Hisar', 'Rohtak'],
            'Andhra Pradesh' => ['Visakhapatnam', 'Vijayawada', 'Guntur', 'Nellore', 'Kurnool', 'Tirupati'],
            'Bihar' => ['Patna', 'Gaya', 'Bhagalpur', 'Muzaffarpur', 'Purnia', 'Darbhanga'],
            'Goa' => ['Panaji', 'Margao', 'Vasco da Gama', 'Mapusa', 'Ponda'],
        ];

        return view('admin.delivery.index', [
            'setting' => $setting,
            'countries' => $countries,
            'indiaStatesWithCities' => $indiaStatesWithCities,
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
