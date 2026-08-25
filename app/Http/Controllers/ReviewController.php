<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Check if the authenticated user has any pending reviews for completed/delivered orders.
     */
    public function pending(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get all orders of the user
        $orders = $user->orders()
            ->with('items.product')
            ->get();

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                // If product is missing from DB, skip
                if (! $item->product) {
                    continue;
                }

                // Check if a review already exists for this order item
                $hasReview = Review::where('order_id', $order->id)
                    ->where('product_id', $item->product_id)
                    ->exists();

                if (! $hasReview) {
                    return response()->json([
                        'has_pending' => true,
                        'order' => [
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                        ],
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'slug' => $item->product->slug,
                            'image' => $item->product->image_path ? asset($item->product->image_path) : asset('images/flavourflow-mark.png'),
                        ],
                    ]);
                }
            }
        }

        return response()->json([
            'has_pending' => false,
        ]);
    }

    /**
     * Submit a product review.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review_text' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $user = $request->user();

        // 1. Verify the order exists and is owned by user
        $order = Order::where('id', $validated['order_id'])
            ->where('user_id', $user->id)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Unauthorized or order not eligible for review.'], 403);
        }

        // 2. Verify product is part of this order
        $hasProduct = $order->items()->where('product_id', $validated['product_id'])->exists();
        if (! $hasProduct) {
            return response()->json(['message' => 'You did not purchase this product in this order.'], 403);
        }

        // 3. Prevent duplicate reviews
        $existing = Review::where('order_id', $validated['order_id'])
            ->where('product_id', $validated['product_id'])
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'You have already reviewed this product for this order.'], 400);
        }

        // 4. Create the review
        // 4. Retrieve names and unit details securely from database
        $orderItem = $order->items()->where('product_id', $validated['product_id'])->first();
        $productName = $orderItem->product_name ?? ($orderItem->product?->name ?? 'Unknown Product');
        $unit = $orderItem->unit ?? ($orderItem->product?->unit ?? '100g');

        // 5. Create the review
        $review = Review::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'order_id' => $validated['order_id'],
            'product_id' => $validated['product_id'],
            'product_name' => $productName,
            'unit' => $unit,
            'rating' => $validated['rating'],
            'review_text' => $validated['review_text'],
        ]);

        // 5. Update the product's community average rating
        $product = Product::find($validated['product_id']);
        if ($product) {
            $average = Review::where('product_id', $product->id)->avg('rating');
            $product->rating = round($average, 1);
            $product->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully!',
            'review' => $review,
        ]);
    }
}
