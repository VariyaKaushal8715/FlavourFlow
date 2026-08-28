<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\PdfReceiptGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('account.orders.index', [
            'site' => config('personal_site'),
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load('items.product');

        return view('account.orders.show', [
            'site' => config('personal_site'),
            'order' => $order,
        ]);
    }

    public function track(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

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
}
