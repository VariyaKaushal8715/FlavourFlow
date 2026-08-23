<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminOrderController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('access-admin');

        $user = $request->user();
        if ($user) {
            $user->forceFill(['admin_orders_last_viewed_at' => now()])->save();
        }

        $search = $request->string('search')->trim()->limit(100)->toString();
        $status = $request->string('status')->trim()->toString();
        $sort = $request->string('sort', 'latest')->trim()->toString();

        $query = Order::query()->with(['user', 'items.product']);

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search): void {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (in_array($status, ['completed', 'pending', 'processing', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        match ($sort) {
            'oldest' => $query->oldest(),
            'amount_high' => $query->orderByDesc('total_amount'),
            'amount_low' => $query->orderBy('total_amount'),
            default => $query->latest(),
        };

        $orders = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Order::count(),
            'completed' => Order::where('status', 'completed')->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'revenue' => (float) Order::where('status', 'completed')->sum('total_amount'),
        ];

        return view('admin.orders.index', [
            'orders' => $orders,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status ?: 'all',
                'sort' => $sort,
            ],
        ]);
    }

    public function show(Order $order): View
    {
        Gate::authorize('access-admin');

        $order->load(['user', 'items.product']);

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    public function unreadSummary(Request $request): JsonResponse
    {
        Gate::authorize('access-admin');

        $user = $request->user();
        $lastViewedAt = $user?->admin_orders_last_viewed_at;

        $query = Order::query();
        if ($lastViewedAt) {
            $query->where('created_at', '>', $lastViewedAt);
        }

        $orderCount = $query->count();
        $customerCount = (clone $query)->whereNotNull('user_id')->distinct('user_id')->count('user_id');

        if ($customerCount === 0 && $orderCount > 0) {
            $customerCount = (clone $query)->distinct('email')->count('email');
        }

        if ($orderCount === 0) {
            return response()->json([
                'has_unread' => false,
                'order_count' => 0,
                'customer_count' => 0,
                'message' => null,
                'orders_url' => route('admin.orders.index'),
            ]);
        }

        $ordersText = $orderCount === 1 ? '1 new order' : "{$orderCount} new orders";
        $customersText = $customerCount === 1 ? '1 customer' : "{$customerCount} customers";
        $message = "You have {$ordersText} from {$customersText}. Tap to view orders.";

        return response()->json([
            'has_unread' => true,
            'order_count' => $orderCount,
            'customer_count' => $customerCount,
            'message' => $message,
            'orders_url' => route('admin.orders.index'),
        ]);
    }

    public function markViewed(Request $request): JsonResponse
    {
        Gate::authorize('access-admin');

        $user = $request->user();
        if ($user) {
            $user->forceFill(['admin_orders_last_viewed_at' => now()])->save();
        }

        return response()->json([
            'success' => true,
            'marked_at' => now()->toIso8601String(),
        ]);
    }
}
