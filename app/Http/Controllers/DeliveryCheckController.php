<?php

namespace App\Http\Controllers;

use App\Models\DeliverySetting;
use App\Models\Product;
use App\Support\CartState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryCheckController extends Controller
{
    /**
     * Check if a given location is deliverable.
     */
    public function check(Request $request, CartState $cart): JsonResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'product_id' => ['nullable', 'integer'],
            'product_slug' => ['nullable', 'string', 'max:150'],
        ]);

        $country = $validated['country'];
        $state = $validated['state'] ?? null;
        $city = $validated['city'] ?? null;

        $setting = DeliverySetting::current();
        $isGlobalDeliverable = $setting->isDeliverable($country, $state, $city);

        if (! $isGlobalDeliverable) {
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

        // Check specific product if requested
        $specificProduct = null;
        if (! empty($validated['product_id'])) {
            $specificProduct = Product::find($validated['product_id']);
        } elseif (! empty($validated['product_slug'])) {
            $specificProduct = Product::where('slug', $validated['product_slug'])->first();
        }

        if ($specificProduct) {
            if (! $specificProduct->isDeliverableTo($country, $state, $city)) {
                return response()->json([
                    'deliverable' => false,
                    'mode' => $setting->mode,
                    'message' => "Sorry, '{$specificProduct->name}' is not deliverable to this location.",
                    'location' => [
                        'country' => $country,
                        'state' => $state,
                        'city' => $city,
                    ],
                ]);
            }
        }

        // If checking cart during checkout, check all cart items
        if ($cart->count() > 0 && ! $specificProduct) {
            foreach ($cart->items() as $item) {
                /** @var Product $product */
                $product = $item['product'];
                if (! $product->isDeliverableTo($country, $state, $city)) {
                    return response()->json([
                        'deliverable' => false,
                        'mode' => $setting->mode,
                        'message' => "Sorry, '{$product->name}' in your cart is not deliverable to this location.",
                        'location' => [
                            'country' => $country,
                            'state' => $state,
                            'city' => $city,
                        ],
                    ]);
                }
            }
        }

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
}
