<x-mail::message>
# Admin Alert: New Order Received

A new order **#{{ $order->order_number }}** has been placed by **{{ $order->name }}**.

<x-mail::panel>
**Order Summary:**
- **Customer:** {{ $order->name }} ({{ $order->email }}, {{ $order->mobile }})
- **Order Number:** #{{ $order->order_number }}
- **Payment Method:** {{ strtoupper($order->payment_method) }}
- **Total Amount:** ₹{{ number_format($order->total_amount, 2) }}
- **Date & Time:** {{ $order->created_at->format('d M Y, h:i A') }}

**Items:**
@foreach ($order->items as $item)
- {{ $item->product_name }} ({{ $item->unit }}) x {{ $item->quantity }} — ₹{{ number_format($item->total_price, 2) }}
@endforeach
</x-mail::panel>

<x-mail::button :url="route('admin.orders.show', $order->id)">
Open Admin Order Panel
</x-mail::button>

FlavourFlow Admin System
</x-mail::message>

