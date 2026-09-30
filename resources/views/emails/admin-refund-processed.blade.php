<x-mail::message>
# Admin Alert: Refund Processed

A refund of **₹{{ number_format($refundRequest->amount, 2) }}** has been processed for Order **#{{ $refundRequest->order->order_number }}**.

<x-mail::panel>
**Refund Summary:**
- **Customer:** {{ $refundRequest->order->name }} ({{ $refundRequest->order->email }})
- **Order Number:** #{{ $refundRequest->order->order_number }}
- **Refund Amount:** ₹{{ number_format($refundRequest->amount, 2) }}
- **Reason:** {{ $refundRequest->reason }}
- **Status:** {{ $refundRequest->status }}
- **Completed Date:** {{ $refundRequest->updated_at->format('d M Y, h:i A') }}
</x-mail::panel>

<x-mail::button :url="route('admin.orders.show', $refundRequest->order->id)">
View Order Panel
</x-mail::button>

FlavourFlow Admin System
</x-mail::message>

