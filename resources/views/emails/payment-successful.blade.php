<x-mail::message>
# Payment Received, {{ $order->name }}!

We've successfully received your payment of **₹{{ number_format($order->total_amount, 2) }}** for order **#{{ $order->order_number }}**.

<x-mail::panel>
**Payment Details:**
- **Transaction / Payment Method:** {{ strtoupper($order->payment_method) }}
- **Total Paid:** ₹{{ number_format($order->total_amount, 2) }}
- **Date & Time:** {{ $order->payment?->paid_at?->format('d M Y, h:i A') ?? now()->format('d M Y, h:i A') }}
</x-mail::panel>

<x-mail::button :url="route('account.orders.show', $order->order_number)">
View Order Status
</x-mail::button>

Thanks for shopping with **FlavourFlow**!
</x-mail::message>

