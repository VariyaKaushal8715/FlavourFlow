<x-mail::message>
# Hello {{ $order->name }},

@if ($status === 'Shipped')
Great news! Your order **#{{ $order->order_number }}** has been shipped and is on its way.
@elseif ($status === 'Out for Delivery')
Your order **#{{ $order->order_number }}** is out for delivery today! Please ensure someone is available at your address.
@elseif ($status === 'Delivered')
Your order **#{{ $order->order_number }}** has been successfully delivered. We hope you enjoy your products!
@elseif ($status === 'Cancelled')
Your order **#{{ $order->order_number }}** has been cancelled.
@if ($order->cancellation_reason)
**Reason:** {{ $order->cancellation_reason }}
@endif
@endif

<x-mail::panel>
**Order Summary:**
- **Order Number:** #{{ $order->order_number }}
- **Status:** {{ $status }}
- **Total Amount:** ₹{{ number_format($order->total_amount, 2) }}
- **Delivery Address:** {{ $order->address }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}
</x-mail::panel>

<x-mail::button :url="route('account.orders.track', $order->order_number)">
Track Order Status
</x-mail::button>

Thank you for choosing **FlavourFlow**!
</x-mail::message>

