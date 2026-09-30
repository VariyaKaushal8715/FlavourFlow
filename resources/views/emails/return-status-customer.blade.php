<x-mail::message>
# Hello {{ $returnRequest->order->name }},

The return request status for your order **#{{ $returnRequest->order->order_number }}** has been updated to **{{ $returnRequest->status }}**.

<x-mail::panel>
**Return Details:**
- **Order Number:** #{{ $returnRequest->order->order_number }}
- **Reason:** {{ $returnRequest->reason }}
- **Current Return Status:** {{ $returnRequest->status }}
- **Updated On:** {{ $returnRequest->updated_at->format('d M Y, h:i A') }}
</x-mail::panel>

<x-mail::button :url="route('account.orders.show', $returnRequest->order->order_number)">
View Order Details
</x-mail::button>

Thank you for shopping with **FlavourFlow**!
</x-mail::message>

