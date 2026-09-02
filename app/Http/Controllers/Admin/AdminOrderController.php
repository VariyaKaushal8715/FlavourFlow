<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\ReturnRequest;
use App\Support\PdfReceiptGenerator;
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

        if ($order->status === 'Cancelled') {
            return redirect()->back()->with('error', 'Once an order is Cancelled, its status cannot be changed.');
        }

        if ($order->returnRequest?->status === 'Approved') {
            return redirect()->back()->with('error', 'Once a return request is Approved, the order status cannot be changed.');
        }

        if ($order->refundRequest?->status === 'Completed') {
            return redirect()->back()->with('error', 'Once a refund is Completed, the order status cannot be changed.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:Confirmed,Shipped,Out for Delivery,Delivered,Cancelled'],
            'cancellation_reason' => ['nullable', 'string', 'required_if:status,Cancelled', 'max:500'],
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
        } elseif ($status === 'Cancelled') {
            $order->cancelled_at = now();
            $order->cancellation_reason = $validated['cancellation_reason'] ?? 'Cancelled by administrator.';
        }

        $order->save();

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    public function updateReturnStatus(Request $request, ReturnRequest $returnRequest)
    {
        Gate::authorize('access-admin');

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:Approved,Rejected'],
        ]);

        $returnRequest->update([
            'status' => $validated['status'],
        ]);

        return redirect()->back()->with('success', 'Return request status updated successfully.');
    }

    public function updateRefundStatus(Request $request, RefundRequest $refundRequest)
    {
        Gate::authorize('access-admin');

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:Completed,Rejected'],
        ]);

        $refundRequest->update([
            'status' => $validated['status'],
        ]);

        return redirect()->back()->with('success', 'Refund request status updated successfully.');
    }

    public function newOrders(Request $request): JsonResponse
    {
        Gate::authorize('access-admin');

        $notifications = [];

        // 1. Pending orders (New Orders)
        $pendingOrders = Order::where('status', 'Pending')->latest()->take(10)->get();
        foreach ($pendingOrders as $order) {
            $notifications[] = [
                'id' => 'new-'.$order->id,
                'order_number' => $order->order_number,
                'name' => '[New Order] Placed by '.$order->name,
                'total_amount' => $order->total_amount,
                'status' => 'Pending',
                'created_at' => $order->created_at->toIso8601String(),
            ];
        }

        // 2. Cancelled orders
        $cancelledOrders = Order::where('status', 'Cancelled')->whereNotNull('cancelled_at')->latest()->take(10)->get();
        foreach ($cancelledOrders as $order) {
            $notifications[] = [
                'id' => 'cancel-'.$order->id,
                'order_number' => $order->order_number,
                'name' => '[Cancelled] '.$order->name.' - '.$order->cancellation_reason,
                'total_amount' => $order->total_amount,
                'status' => 'Cancelled',
                'created_at' => $order->cancelled_at->toIso8601String(),
            ];
        }

        // 3. Return Requests
        $returns = ReturnRequest::with('order')->latest()->take(10)->get();
        foreach ($returns as $ret) {
            if ($ret->order) {
                $notifications[] = [
                    'id' => 'return-'.$ret->id,
                    'order_number' => $ret->order->order_number,
                    'name' => '[Return Request] ('.$ret->status.') '.$ret->order->name.' - '.$ret->reason,
                    'total_amount' => $ret->order->total_amount,
                    'status' => 'Return requested',
                    'created_at' => $ret->created_at->toIso8601String(),
                ];
            }
        }

        // 4. Refund Requests
        $refunds = RefundRequest::with('order')->latest()->take(10)->get();
        foreach ($refunds as $ref) {
            if ($ref->order) {
                $notifications[] = [
                    'id' => 'refund-'.$ref->id,
                    'order_number' => $ref->order->order_number,
                    'name' => '[Refund Request] ('.$ref->status.') '.$ref->order->name.' - '.$ref->reason,
                    'total_amount' => $ref->order->total_amount,
                    'status' => 'Refund requested',
                    'created_at' => $ref->created_at->toIso8601String(),
                ];
            }
        }

        // Sort notifications by created_at desc
        usort($notifications, function ($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });

        // Limit to 10
        $notifications = array_slice($notifications, 0, 10);

        return response()->json([
            'orders' => $notifications,
        ]);
    }

    public function downloadReceipt(Request $request, Order $order)
    {
        Gate::authorize('access-admin');

        $order->load(['items.product', 'user']);

        $pdfGenerator = new PdfReceiptGenerator;
        $pdfContent = $pdfGenerator->generate($order);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Receipt_'.$order->order_number.'.pdf"',
            'Content-Length' => strlen($pdfContent),
        ]);
    }
}
