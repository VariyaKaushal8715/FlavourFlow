@php
    $latestPayment = $payment ?? $order->payments->first();
    $paymentStatus = $latestPayment?->status ?? ($order->payment_method === 'cod' ? 'Cash on delivery' : 'Pending');
    $address = collect([$order->address, $order->city, $order->state, $order->pincode, $order->country])->filter()->implode(', ');
@endphp

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:18px 0;">
    <tr>
        <td style="padding:6px 0;color:#555;width:42%;">Customer</td>
        <td style="padding:6px 0;">{{ $order->name }} ({{ $order->email }})</td>
    </tr>
    <tr>
        <td style="padding:6px 0;color:#555;">Order ID</td>
        <td style="padding:6px 0;">{{ $order->order_number }}</td>
    </tr>
    <tr>
        <td style="padding:6px 0;color:#555;">Payment status</td>
        <td style="padding:6px 0;">{{ str($paymentStatus)->headline() }}</td>
    </tr>
    <tr>
        <td style="padding:6px 0;color:#555;">Order status</td>
        <td style="padding:6px 0;">{{ $order->status }}</td>
    </tr>
    <tr>
        <td style="padding:6px 0;color:#555;">Shipping address</td>
        <td style="padding:6px 0;">{{ $address }}</td>
    </tr>
    <tr>
        <td style="padding:6px 0;color:#555;">Order date</td>
        <td style="padding:6px 0;">{{ $order->created_at?->format('d M Y, h:i A') }}</td>
    </tr>
</table>
