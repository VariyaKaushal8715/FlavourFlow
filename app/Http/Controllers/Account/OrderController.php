<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->withCount('items')
            ->latest()
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

        $steps = $this->buildTrackingSteps($order);

        return view('account.orders.track', [
            'site' => config('personal_site'),
            'order' => $order,
            'steps' => $steps,
        ]);
    }

    /**
     * Build an ordered list of tracking steps based on the order status.
     *
     * @return list<array{label: string, description: string, state: string, time: ?Carbon}>
     */
    private function buildTrackingSteps(Order $order): array
    {
        $statusSequence = ['Confirmed', 'Processing', 'Shipped', 'Out for Delivery', 'Delivered'];
        $currentIndex = array_search($order->status, $statusSequence, true);

        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        $descriptions = [
            'Confirmed' => 'Your order has been confirmed and is being prepared.',
            'Processing' => 'Your order is being packed with care.',
            'Shipped' => 'Your order has been shipped and is on its way.',
            'Out for Delivery' => 'Your order is out for delivery and will arrive soon.',
            'Delivered' => 'Your order has been delivered successfully.',
        ];

        $steps = [];

        foreach ($statusSequence as $index => $status) {
            if ($index < $currentIndex) {
                $state = 'completed';
                $time = $order->created_at->copy()->addHours($index * 6);
            } elseif ($index === $currentIndex) {
                $state = 'active';
                $time = $index === 0 ? $order->created_at : $order->updated_at;
            } else {
                $state = 'pending';
                $time = null;
            }

            $steps[] = [
                'label' => $status,
                'description' => $descriptions[$status],
                'state' => $state,
                'time' => $time,
            ];
        }

        return $steps;
    }
}
