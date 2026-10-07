<x-mail::message>
# Hello {{ $refundRequest->order->name }},

Your refund request for order **#{{ $refundRequest->order->order_number }}** has been processed successfully.

<x-mail::panel>
**Refund Details:**
- **Order Number:** #{{ $refundRequest->order->order_number }}
- **Refund Amount:** ₹{{ number_format($refundRequest->amount, 2) }}
- **Reason:** {{ $refundRequest->reason }}
- **Status:** {{ $refundRequest->status }}
- **Processed Date:** {{ $refundRequest->updated_at->format('d M Y, h:i A') }}
</x-mail::panel>

<x-mail::button :url="route('account.orders.show', $refundRequest->order->order_number)">
View Order Details
</x-mail::button>

Thank you for choosing **FlavourFlow**!
</x-mail::message>

