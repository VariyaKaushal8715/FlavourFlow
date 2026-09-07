<?php

namespace App\Http\Controllers;

use App\Models\DeliverySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryCheckController extends Controller
{
    /**
     * Check if a given location is deliverable.
     */
    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        $country = $validated['country'];
        $state = $validated['state'] ?? null;
        $city = $validated['city'] ?? null;

        $setting = DeliverySetting::current();
        $isDeliverable = $setting->isDeliverable($country, $state, $city);

        if ($isDeliverable) {
            $locationParts = array_filter([$city, $state, $country]);
            $locationName = implode(', ', $locationParts);

            return response()->json([
                'deliverable' => true,
                'mode' => $setting->mode,
                'message' => $locationName ? "Deliverable to {$locationName}!" : 'Deliverable to this location!',
                'location' => [
                    'country' => $country,
                    'state' => $state,
                    'city' => $city,
                ],
            ]);
        }

        return response()->json([
            'deliverable' => false,
            'mode' => $setting->mode,
            'message' => 'Sorry, we don’t deliver to this location.',
            'location' => [
                'country' => $country,
                'state' => $state,
                'city' => $city,
            ],
        ]);
    }
}
