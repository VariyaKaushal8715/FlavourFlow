<x-mail::message>
# Hello {{ $returnRequest->order->name }},

We've received your return request for Order **#{{ $returnRequest->order->order_number }}**. Our team is currently reviewing your request.

<x-mail::panel>
**Return Request Details:**
- **Order Number:** #{{ $returnRequest->order->order_number }}
- **Return Reason:** {{ $returnRequest->reason }}
- **Status:** {{ $returnRequest->status }}
- **Submitted On:** {{ $returnRequest->created_at->format('d M Y, h:i A') }}
</x-mail::panel>

<x-mail::button :url="route('account.orders.show', $returnRequest->order->order_number)">
View Order Details
</x-mail::button>

We will notify you once your return status is updated.

Thanks for choosing **FlavourFlow**!
</x-mail::message>

