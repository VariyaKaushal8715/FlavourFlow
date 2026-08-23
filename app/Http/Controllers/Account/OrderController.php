<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
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

        foreach ($steps as $index => &$step) {
            if ($index < $currentIndex) {
                $step['state'] = 'completed';
                $step['time'] = $index === 0 ? $order->created_at : $order->updated_at;
            } elseif ($index === $currentIndex) {
                $step['state'] = 'active';
                $step['time'] = $order->updated_at;
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
}
