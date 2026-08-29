<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Check if the authenticated user has any pending reviews for orders.
     * Returns the order and the list of unreviewed products in that order.
     */
    public function pending(Request $request): JsonResponse
    {
        $user = $request->user();

        // Find the first order that has at least one unreviewed product
        $orders = $user->orders()
            ->with('items.product')
            ->get();

        foreach ($orders as $order) {
            $pendingProducts = [];

            foreach ($order->items as $item) {
                if (! $item->product) {
                    continue;
                }

                // Check if this item has already been reviewed
                $hasReview = Review::where('order_id', $order->id)
                    ->where('product_id', $item->product_id)
                    ->exists();

                if (! $hasReview) {
                    $pendingProducts[] = [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'slug' => $item->product->slug,
                        'image' => $item->product->image_path ? asset($item->product->image_path) : asset('images/flavourflow-mark.png'),
                    ];
                }
            }

            if (count($pendingProducts) > 0) {
                return response()->json([
                    'has_pending' => true,
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                    ],
                    'products' => $pendingProducts,
                ]);
            }
        }

        return response()->json([
            'has_pending' => false,
        ]);
    }

    /**
     * Submit multiple product reviews for a single order.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'reviews' => ['required', 'array', 'min:1'],
            'reviews.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'reviews.*.rating' => ['required', 'integer', 'min:1', 'max:5'],
            'reviews.*.review_text' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $user = $request->user();

        // 1. Verify the order exists and is owned by the user
        $order = Order::where('id', $validated['order_id'])
            ->where('user_id', $user->id)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Unauthorized or order not eligible for review.'], 403);
        }

        $createdReviews = [];

        // Use transaction to save all reviews safely
        DB::transaction(function () use ($validated, $order, $user, &$createdReviews) {
            foreach ($validated['reviews'] as $revData) {
                // Verify product is part of this order
                $hasProduct = $order->items()->where('product_id', $revData['product_id'])->exists();
                if (! $hasProduct) {
                    abort(response()->json(['message' => 'You did not purchase this product in this order.'], 403));
                }

                // Prevent duplicate reviews
                $existing = Review::where('order_id', $validated['order_id'])
                    ->where('product_id', $revData['product_id'])
                    ->exists();

                if ($existing) {
                    abort(response()->json(['message' => 'You have already reviewed this product for this order.'], 400));
                }

                // Retrieve names and unit details securely from database
                $orderItem = $order->items()->where('product_id', $revData['product_id'])->first();
                $productName = $orderItem->product_name ?? ($orderItem->product?->name ?? 'Unknown Product');
                $unit = $orderItem->unit ?? ($orderItem->product?->unit ?? '100g');

                // Create the review
                $review = Review::create([
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'order_id' => $order->id,
                    'product_id' => $revData['product_id'],
                    'product_name' => $productName,
                    'unit' => $unit,
                    'rating' => $revData['rating'],
                    'review_text' => $revData['review_text'],
                ]);

                $createdReviews[] = $review;

                // Update product average rating
                $product = Product::find($revData['product_id']);
                if ($product) {
                    $average = Review::where('product_id', $product->id)->avg('rating');
                    $product->rating = round($average, 1);
                    $product->save();
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Reviews submitted successfully!',
            'reviews' => $createdReviews,
        ]);
    }
}
