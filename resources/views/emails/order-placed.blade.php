<x-mail::message>
# Thank you for your order, {{ $order->name }}!

We've received your order **#{{ $order->order_number }}** and are getting it ready for packing.

<x-mail::panel>
**Order Summary:**
@foreach ($order->items as $item)
- **{{ $item->product_name }}** ({{ $item->unit }}) x {{ $item->quantity }} — ₹{{ number_format($item->total_price, 2) }}
@endforeach

**Subtotal:** ₹{{ number_format($order->subtotal, 2) }}  
@if ($order->discount_amount > 0)
**Discount:** -₹{{ number_format($order->discount_amount, 2) }}  
@endif
**Delivery Charge:** ₹{{ number_format($order->delivery_charge, 2) }}  
**Total Amount:** ₹{{ number_format($order->total_amount, 2) }}  
**Payment Method:** {{ strtoupper($order->payment_method) }}
</x-mail::panel>

**Delivery Address:**  
{{ $order->address }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}

<x-mail::button :url="route('account.orders.show', $order->order_number)">
View Order Details
</x-mail::button>

If you have any questions, reply directly to this email or contact us at {{ config('mail.from.address') }}.

Thanks for choosing **FlavourFlow**!
</x-mail::message>
