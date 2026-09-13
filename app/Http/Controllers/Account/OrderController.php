<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderNotification;
use App\Models\RefundRequest;
use App\Models\ReturnRequest;
use App\Support\PdfReceiptGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $orders = $user->orders()
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->get();

        $notifications = $user->orderNotifications()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('account.orders.index', [
            'site' => config('personal_site'),
            'orders' => $orders,
            'notifications' => $notifications,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load('items.product');

        // Mark order notifications as read
        $request->user()->orderNotifications()
            ->where('order_id', $order->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('account.orders.show', [
            'site' => config('personal_site'),
            'order' => $order,
        ]);
    }

    public function track(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        // Mark order notifications as read
        $request->user()->orderNotifications()
            ->where('order_id', $order->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($order->status === 'Cancelled') {
            $steps = [
                [
                    'name' => 'Confirmed',
                    'label' => 'Order Confirmed',
                    'description' => 'Your order has been placed.',
                    'state' => 'completed',
                    'time' => $order->confirmed_at ?? $order->created_at,
                ],
                [
                    'name' => 'Cancelled',
                    'label' => 'Order Cancelled',
                    'description' => 'Reason: '.($order->cancellation_reason ?? 'Cancelled by admin'),
                    'state' => 'active',
                    'time' => $order->cancelled_at ?? $order->updated_at,
                ],
            ];
        } else {
            $steps = [
                [
                    'name' => 'Confirmed',
                    'label' => 'Order Confirmed',
                    'description' => 'Your order has been placed and confirmed.',
                ],
                [
                    'name' => 'Shipped',
                    'label' => 'Shipped',
                    'description' => 'Your package has been handed over to our courier partner.',
                ],
                [
                    'name' => 'Out for Delivery',
                    'label' => 'Out for Delivery',
                    'description' => 'Our delivery partner is on the way to your address.',
                ],
                [
                    'name' => 'Delivered',
                    'label' => 'Delivered',
                    'description' => 'The package has been successfully delivered.',
                ],
            ];

            $statusList = array_column($steps, 'name');
            $currentIndex = array_search($order->status, $statusList);
            if ($currentIndex === false) {
                $currentIndex = 0;
            }

            $times = [
                'Confirmed' => $order->confirmed_at ?? $order->created_at,
                'Shipped' => $order->shipped_at,
                'Out for Delivery' => $order->out_for_delivery_at,
                'Delivered' => $order->delivered_at,
            ];

            if ($order->status === 'Delivered') {
                foreach ($steps as &$step) {
                    $step['state'] = 'completed';
                    $step['time'] = $times[$step['name']] ?? $order->delivered_at;
                }
            } else {
                foreach ($steps as $index => &$step) {
                    $stepTime = $times[$step['name']];
                    if ($index < $currentIndex) {
                        $step['state'] = 'completed';
                        $step['time'] = $stepTime ?? $order->created_at;
                    } elseif ($index === $currentIndex) {
                        $step['state'] = 'active';
                        $step['time'] = $stepTime ?? $order->updated_at;
                    } else {
                        $step['state'] = 'pending';
                        $step['time'] = null;
                    }
                }
            }
        }

        return view('account.orders.track', [
            'site' => config('personal_site'),
            'order' => $order,
            'steps' => $steps,
        ]);
    }

    public function downloadReceipt(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load(['items.product', 'user']);

        $pdfGenerator = new PdfReceiptGenerator;
        $pdfContent = $pdfGenerator->generate($order);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Receipt_'.$order->order_number.'.pdf"',
            'Content-Length' => strlen($pdfContent),
        ]);
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->status !== 'Confirmed') {
            return redirect()->back()->with('error', 'Only confirmed orders can be cancelled.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $order->update([
            'status' => 'Cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['reason'],
        ]);

        return redirect()->back()->with('success', 'Order cancelled successfully.');
    }

    public function requestReturn(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->status !== 'Delivered') {
            return redirect()->back()->with('error', 'Only delivered orders can be returned.');
        }

        if (! $order->delivered_at || $order->delivered_at->diffInDays(now()) > 7) {
            return redirect()->back()->with('error', 'The return period for this order has expired.');
        }

        if ($order->returnRequest()->exists() || $order->refundRequest()->exists()) {
            return redirect()->back()->with('error', 'A return or refund request already exists for this order.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        ReturnRequest::create([
            'order_id' => $order->id,
            'reason' => $validated['reason'],
            'status' => 'Pending',
        ]);

        return redirect()->back()->with('success', 'Return request submitted successfully.');
    }

    public function requestRefund(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->status !== 'Delivered') {
            return redirect()->back()->with('error', 'Refund requests can only be placed for delivered orders.');
        }

        if (! $order->delivered_at || $order->delivered_at->diffInDays(now()) > 7) {
            return redirect()->back()->with('error', 'The refund period for this order has expired.');
        }

        if ($order->returnRequest()->exists() || $order->refundRequest()->exists()) {
            return redirect()->back()->with('error', 'A return or refund request already exists for this order.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        RefundRequest::create([
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'reason' => $validated['reason'],
            'status' => 'Pending',
        ]);

        return redirect()->back()->with('success', 'Refund request submitted successfully.');
    }

    public function sse(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        return response()->stream(function () use ($user) {
            $lastId = OrderNotification::where('user_id', $user->id)->max('id') ?? 0;
            // Send initial connection message
            echo "event: connected\ndata: {}\n\n";
            ob_flush();
            flush();

            while (true) {
                if (connection_aborted()) {
                    break;
                }

                $newNotifications = OrderNotification::where('user_id', $user->id)
                    ->where('id', '>', $lastId)
                    ->orderBy('id', 'asc')
                    ->get();

                if ($newNotifications->isNotEmpty()) {
                    foreach ($newNotifications as $notif) {
                        $lastId = $notif->id;

                        $payload = json_encode([
                            'id' => $notif->id,
                            'order_number' => $notif->order->order_number,
                            'message' => $notif->message,
                            'status' => $notif->status,
                            'created_at' => $notif->created_at->diffForHumans(),
                            'url' => route('account.orders.show', $notif->order->order_number),
                            'unread_count' => OrderNotification::where('user_id', $user->id)->whereNull('read_at')->count(),
                        ]);

                        echo "data: {$payload}\n\n";
                        ob_flush();
                        flush();
                    }
                }

                sleep(1);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
