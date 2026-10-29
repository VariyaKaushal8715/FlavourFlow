<x-mail::message>
# Order Confirmation Code for Order #{{ $order->order_number }}

Hello {{ $order->name }},

Your payment of **₹{{ number_format($order->total_amount, 2) }}** for order **#{{ $order->order_number }}** has been successfully verified!

Here is your exclusive order confirmation and delivery code:

<x-mail::panel>
<div style="text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #b42318; padding: 12px 0;">
{{ $otp }}
</div>
<p style="text-align: center; margin: 0; font-size: 12px; color: #71717a;">
Valid for 15 minutes. Use this code to verify your delivery and order milestones.
</p>
</x-mail::panel>

**Order Summary:**
- **Order Number:** #{{ $order->order_number }}
- **Payment Method:** {{ strtoupper($order->payment_method) }}
- **Total Amount:** ₹{{ number_format($order->total_amount, 2) }}
- **Delivery Address:** {{ $order->address }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}

<x-mail::button :url="route('account.orders.show', $order->order_number)">
View Order Details
</x-mail::button>

If you did not initiate this order, please contact our support team immediately at **{{ config('mail.admin_address', 'support@flavourflow.test') }}**.

Warm regards,<br>
**The FlavourFlow Spices Team**
</x-mail::message>

