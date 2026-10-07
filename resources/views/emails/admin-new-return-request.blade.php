<x-mail::message>
# Admin Alert: New Return Request Submitted

A return request has been submitted for Order **#{{ $returnRequest->order->order_number }}**.

<x-mail::panel>
**Return Request Summary:**
- **Customer:** {{ $returnRequest->order->name }} ({{ $returnRequest->order->email }})
- **Order Number:** #{{ $returnRequest->order->order_number }}
- **Reason:** {{ $returnRequest->reason }}
- **Status:** {{ $returnRequest->status }}
- **Submitted On:** {{ $returnRequest->created_at->format('d M Y, h:i A') }}
</x-mail::panel>

<x-mail::button :url="route('admin.orders.show', $returnRequest->order->id)">
Review Return Request
</x-mail::button>

FlavourFlow Admin System
</x-mail::message>

