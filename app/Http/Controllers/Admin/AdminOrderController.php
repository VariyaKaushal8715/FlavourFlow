<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
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
}

