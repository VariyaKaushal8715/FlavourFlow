<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - {{ $storeInfo['name'] ?? 'FlavourFlow' }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);" cellspacing="0" cellpadding="0">
                    
                    {{-- Header / Brand --}}
                    <tr>
                        <td style="background-color: #b42318; padding: 24px 32px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.5px;">
                                {{ $storeInfo['name'] ?? 'FlavourFlow' }}
                            </h1>
                            <p style="color: #fef08a; margin: 6px 0 0 0; font-size: 14px; font-weight: 500;">
                                Pure spices. Pure love.
                            </p>
                        </td>
                    </tr>

                    {{-- Main Body --}}
                    <tr>
                        <td style="padding: 32px;">
                            <div style="text-align: center; margin-bottom: 24px;">
                                <div style="display: inline-block; background-color: #ecfdf5; color: #047857; padding: 8px 16px; border-radius: 9999px; font-size: 14px; font-weight: 600;">
                                    ✓ Order Confirmed! 🎉
                                </div>
                            </div>

                            <p style="font-size: 16px; line-height: 24px; margin: 0 0 16px 0;">
                                Hi <strong>{{ $order->name }}</strong>,
                            </p>
                            <p style="font-size: 15px; line-height: 24px; color: #475569; margin: 0 0 24px 0;">
                                Thank you for your order! Your FlavourFlow order <strong>#{{ $order->order_number }}</strong> has been placed and confirmed successfully.
                            </p>

                            {{-- Order Meta Box --}}
                            <table role="presentation" width="100%" style="background-color: #f8fafc; border-radius: 12px; padding: 16px; margin-bottom: 24px; border: 1px solid #e2e8f0;" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding: 6px 0; font-size: 14px; color: #64748b;">Order Number:</td>
                                    <td style="padding: 6px 0; font-size: 14px; font-weight: 600; text-align: right; color: #0f172a;">#{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; font-size: 14px; color: #64748b;">Order Date:</td>
                                    <td style="padding: 6px 0; font-size: 14px; text-align: right; color: #0f172a;">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; font-size: 14px; color: #64748b;">Current Status:</td>
                                    <td style="padding: 6px 0; font-size: 14px; font-weight: 600; text-align: right; color: #0284c7;">{{ $order->status }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; font-size: 14px; color: #64748b;">Payment Method:</td>
                                    <td style="padding: 6px 0; font-size: 14px; text-align: right; color: #0f172a; text-transform: uppercase;">{{ $order->payment_method }}</td>
                                </tr>
                            </table>

                            {{-- Items Table --}}
                            <h3 style="font-size: 16px; font-weight: 600; color: #0f172a; margin: 0 0 12px 0;">
                                Order Items
                            </h3>
                            <table role="presentation" width="100%" style="border-collapse: collapse; margin-bottom: 24px;" cellspacing="0" cellpadding="0">
                                <thead>
                                    <tr style="border-bottom: 2px solid #e2e8f0;">
                                        <th style="padding: 8px 0; text-align: left; font-size: 13px; color: #64748b; font-weight: 600;">Product</th>
                                        <th style="padding: 8px 0; text-align: center; font-size: 13px; color: #64748b; font-weight: 600;">Qty</th>
                                        <th style="padding: 8px 0; text-align: right; font-size: 13px; color: #64748b; font-weight: 600;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td style="padding: 12px 0; font-size: 14px; color: #1e293b;">
                                                <div style="font-weight: 600;">{{ $item->product_name }}</div>
                                                @if($item->unit)
                                                    <div style="font-size: 12px; color: #64748b;">Pack: {{ $item->unit }}</div>
                                                @endif
                                            </td>
                                            <td style="padding: 12px 0; font-size: 14px; text-align: center; color: #475569;">
                                                {{ $item->quantity }}
                                            </td>
                                            <td style="padding: 12px 0; font-size: 14px; font-weight: 600; text-align: right; color: #0f172a;">
                                                ₹{{ number_format($item->total_price, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            {{-- Financial Summary --}}
                            <table role="presentation" width="100%" style="margin-bottom: 24px;" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding: 4px 0; font-size: 14px; color: #64748b;">Subtotal:</td>
                                    <td style="padding: 4px 0; font-size: 14px; text-align: right; color: #0f172a;">₹{{ number_format($order->subtotal, 2) }}</td>
                                </tr>
                                @if($order->discount_amount > 0)
                                    <tr>
                                        <td style="padding: 4px 0; font-size: 14px; color: #16a34a;">Discount ({{ $order->coupon_code }}):</td>
                                        <td style="padding: 4px 0; font-size: 14px; text-align: right; color: #16a34a;">-₹{{ number_format($order->discount_amount, 2) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding: 4px 0; font-size: 14px; color: #64748b;">Delivery Fee:</td>
                                    <td style="padding: 4px 0; font-size: 14px; text-align: right; color: #0f172a;">
                                        {{ $order->delivery_charge == 0 ? 'FREE' : '₹' . number_format($order->delivery_charge, 2) }}
                                    </td>
                                </tr>
                                <tr style="border-top: 1px solid #cbd5e1;">
                                    <td style="padding: 10px 0; font-size: 16px; font-weight: 700; color: #0f172a;">Grand Total:</td>
                                    <td style="padding: 10px 0; font-size: 18px; font-weight: 700; text-align: right; color: #b42318;">
                                        ₹{{ number_format($order->total_amount, 2) }}
                                    </td>
                                </tr>
                            </table>

                            {{-- Delivery Address --}}
                            <div style="background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 12px; padding: 16px; margin-bottom: 28px;">
                                <h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600; color: #92400e;">
                                    📍 Delivery Address
                                </h4>
                                <p style="margin: 0; font-size: 14px; line-height: 20px; color: #78350f;">
                                    {{ $order->address }}<br>
                                    {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}<br>
                                    {{ $order->country }}
                                </p>
                            </div>

                            {{-- Track Order CTA --}}
                            <div style="text-align: center; margin-bottom: 32px;">
                                <a href="{{ $trackingUrl }}" style="display: inline-block; background-color: #b42318; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 15px; padding: 14px 28px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(180, 35, 24, 0.2);">
                                    Track Your Order →
                                </a>
                                <p style="margin: 8px 0 0 0; font-size: 12px; color: #94a3b8;">
                                    Or copy this secure link: <br>
                                    <span style="word-break: break-all; color: #64748b;">{{ $trackingUrl }}</span>
                                </p>
                            </div>

                            {{-- Store / Seller Contact Info --}}
                            <div style="border-top: 1px solid #e2e8f0; padding-top: 20px; font-size: 13px; color: #64748b;">
                                <h4 style="margin: 0 0 6px 0; font-size: 13px; font-weight: 600; color: #334155;">
                                    Seller & Customer Support Contact:
                                </h4>
                                <p style="margin: 0; line-height: 20px;">
                                    <strong>{{ $storeInfo['name'] ?? 'FlavourFlow' }}</strong><br>
                                    Phone: {{ $storeInfo['phone'] ?? '+91 99999 99999' }}<br>
                                    WhatsApp: {{ $storeInfo['whatsapp'] ?? '+91 99999 99999' }}<br>
                                    Email: <a href="mailto:{{ $storeInfo['email'] ?? 'support@flavourflow.com' }}" style="color: #b42318;">{{ $storeInfo['email'] ?? 'support@flavourflow.com' }}</a>
                                </p>
                            </div>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f8fafc; padding: 20px 32px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;">
                            &copy; {{ date('Y') }} {{ $storeInfo['name'] ?? 'FlavourFlow' }}. All rights reserved.<br>
                            Thank you for shopping with us!
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
