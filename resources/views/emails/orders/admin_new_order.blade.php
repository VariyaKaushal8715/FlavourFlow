<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Received - {{ $storeInfo['name'] ?? 'FlavourFlow' }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f172a; color: #1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #0f172a; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #334155; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);" cellspacing="0" cellpadding="0">
                    
                    {{-- Header / Alert Banner --}}
                    <tr>
                        <td style="background-color: #0f172a; padding: 24px 32px; border-bottom: 4px solid #b42318;">
                            <table width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td>
                                        <div style="color: #fbbf24; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                                            Store Alert
                                        </div>
                                        <h1 style="color: #ffffff; margin: 4px 0 0 0; font-size: 22px; font-weight: 700;">
                                            🚨 New Order Received!
                                        </h1>
                                    </td>
                                    <td align="right">
                                        <div style="background-color: #1e293b; border: 1px solid #475569; color: #f8fafc; padding: 6px 12px; border-radius: 8px; font-size: 14px; font-weight: 700;">
                                            #{{ $order->order_number }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Main Body --}}
                    <tr>
                        <td style="padding: 32px;">
                            
                            {{-- Key Metrics Box --}}
                            <table role="presentation" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px;" cellspacing="0" cellpadding="12">
                                <tr>
                                    <td width="33%" style="text-align: center; border-right: 1px solid #e2e8f0;">
                                        <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Total Value</div>
                                        <div style="font-size: 18px; font-weight: 700; color: #b42318; margin-top: 4px;">₹{{ number_format($order->total_amount, 2) }}</div>
                                    </td>
                                    <td width="33%" style="text-align: center; border-right: 1px solid #e2e8f0;">
                                        <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Payment</div>
                                        <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 4px; text-transform: uppercase;">{{ $order->payment_method }}</div>
                                    </td>
                                    <td width="33%" style="text-align: center;">
                                        <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Status</div>
                                        <div style="font-size: 14px; font-weight: 700; color: #0284c7; margin-top: 4px;">{{ $order->status }}</div>
                                    </td>
                                </tr>
                            </table>

                            {{-- Customer Details --}}
                            <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 12px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                👤 Customer Information
                            </h3>
                            <table role="presentation" width="100%" style="background-color: #f1f5f9; border-radius: 8px; padding: 14px; margin-bottom: 24px;" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding: 4px 0; font-size: 14px; color: #475569;" width="35%">Name:</td>
                                    <td style="padding: 4px 0; font-size: 14px; font-weight: 600; color: #0f172a;">{{ $order->name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0; font-size: 14px; color: #475569;">Email:</td>
                                    <td style="padding: 4px 0; font-size: 14px; font-weight: 600; color: #0f172a;">
                                        <a href="mailto:{{ $order->email }}" style="color: #2563eb;">{{ $order->email }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0; font-size: 14px; color: #475569;">Phone / Mobile:</td>
                                    <td style="padding: 4px 0; font-size: 14px; font-weight: 600; color: #0f172a;">
                                        {{ $order->mobile ?? 'Not provided' }}
                                    </td>
                                </tr>
                            </table>

                            {{-- Products List --}}
                            <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 12px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                📦 Ordered Products
                            </h3>
                            <table role="presentation" width="100%" style="border-collapse: collapse; margin-bottom: 24px;" cellspacing="0" cellpadding="0">
                                <thead>
                                    <tr style="border-bottom: 2px solid #e2e8f0;">
                                        <th style="padding: 8px 0; text-align: left; font-size: 13px; color: #64748b; font-weight: 600;">Product</th>
                                        <th style="padding: 8px 0; text-align: center; font-size: 13px; color: #64748b; font-weight: 600;">Qty</th>
                                        <th style="padding: 8px 0; text-align: right; font-size: 13px; color: #64748b; font-weight: 600;">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td style="padding: 10px 0; font-size: 14px; color: #1e293b;">
                                                <strong>{{ $item->product_name }}</strong>
                                                @if($item->sku)
                                                    <span style="font-size: 12px; color: #64748b;">(SKU: {{ $item->sku }})</span>
                                                @endif
                                            </td>
                                            <td style="padding: 10px 0; font-size: 14px; text-align: center; color: #334155; font-weight: 600;">
                                                {{ $item->quantity }}
                                            </td>
                                            <td style="padding: 10px 0; font-size: 14px; text-align: right; font-weight: 600; color: #0f172a;">
                                                ₹{{ number_format($item->total_price, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            {{-- Delivery Address --}}
                            <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 8px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                📍 Delivery Address
                            </h3>
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 28px; font-size: 14px; line-height: 22px; color: #334155;">
                                {{ $order->address }}<br>
                                {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}<br>
                                {{ $order->country }}
                            </div>

                            {{-- Admin View CTA --}}
                            <div style="text-align: center; margin-bottom: 16px;">
                                <a href="{{ $adminOrderUrl }}" style="display: inline-block; background-color: #0f172a; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 15px; padding: 14px 28px; border-radius: 8px;">
                                    View & Process Order in Admin Panel →
                                </a>
                            </div>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f8fafc; padding: 16px 32px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b;">
                            FlavourFlow Admin Notification System &bull; {{ now()->format('d M Y, h:i A') }}
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
