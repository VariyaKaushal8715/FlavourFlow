<x-mail::message>
# Admin Alert: Order Cancelled

Order **#{{ $order->order_number }}** has been cancelled.

<x-mail::panel>
**Cancellation Details:**
- **Customer:** {{ $order->name }} ({{ $order->email }})
- **Order Number:** #{{ $order->order_number }}
- **Total Amount:** ₹{{ number_format($order->total_amount, 2) }}
- **Reason:** {{ $order->cancellation_reason ?? 'No reason provided' }}
- **Cancelled At:** {{ $order->cancelled_at?->format('d M Y, h:i A') ?? now()->format('d M Y, h:i A') }}
</x-mail::panel>

<x-mail::button :url="route('admin.orders.show', $order->id)">
Open Admin Order Panel
</x-mail::button>

FlavourFlow Admin System
</x-mail::message>

