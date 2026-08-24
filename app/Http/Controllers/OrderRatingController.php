<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderRatingController extends Controller
{
    public function store(Request $request, Order $order): JsonResponse
    {
        if (! $request->user() || (int) $order->user_id !== (int) $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to rate this order.',
            ], 403);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $order->update([
            'rating' => (int) $validated['rating'],
            'feedback' => $validated['feedback'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
            'rating' => (int) $order->rating,
        ]);
    }
}
