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

    public function updateStatus(Request $request, Order $order)
    {
        Gate::authorize('access-admin');

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:Confirmed,Shipped,Out for Delivery,Delivered'],
        ]);

        $status = $validated['status'];
        $order->status = $status;

        // Set the corresponding timestamp
        if ($status === 'Confirmed') {
            $order->confirmed_at = now();
        } elseif ($status === 'Shipped') {
            $order->shipped_at = now();
        } elseif ($status === 'Out for Delivery') {
            $order->out_for_delivery_at = now();
        } elseif ($status === 'Delivered') {
            $order->delivered_at = now();
        }

        $order->save();

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    public function newOrders(Request $request): JsonResponse
    {
        Gate::authorize('access-admin');

        // Fetch orders created recently, latest first
        $orders = Order::query()
            ->latest()
            ->take(10)
            ->get(['id', 'order_number', 'name', 'total_amount', 'status', 'created_at']);

        return response()->json([
            'orders' => $orders,
        ]);
    }
}
