<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:18px 0;">
    <tr>
        <td style="font-weight:700;padding:10px 0;border-bottom:1px solid #ece5d8;">Product</td>
        <td align="center" style="font-weight:700;padding:10px 0;border-bottom:1px solid #ece5d8;">Qty</td>
        <td align="right" style="font-weight:700;padding:10px 0;border-bottom:1px solid #ece5d8;">Price</td>
    </tr>
    @foreach ($order->items as $item)
        <tr>
            <td style="padding:10px 0;border-bottom:1px solid #f1ece2;">
                {{ $item->product_name }}
                @if ($item->unit)
                    <div style="color:#777;font-size:12px;">{{ $item->unit }}</div>
                @endif
            </td>
            <td align="center" style="padding:10px 0;border-bottom:1px solid #f1ece2;">{{ $item->quantity }}</td>
            <td align="right" style="padding:10px 0;border-bottom:1px solid #f1ece2;">Rs. {{ number_format((float) $item->total_price, 2) }}</td>
        </tr>
    @endforeach
</table>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:18px 0;">
    <tr>
        <td style="padding:5px 0;color:#555;">Subtotal</td>
        <td align="right" style="padding:5px 0;">Rs. {{ number_format((float) $order->subtotal, 2) }}</td>
    </tr>
    <tr>
        <td style="padding:5px 0;color:#555;">Shipping</td>
        <td align="right" style="padding:5px 0;">Rs. {{ number_format((float) $order->delivery_charge, 2) }}</td>
    </tr>
    <tr>
        <td style="padding:5px 0;color:#555;">Discount</td>
        <td align="right" style="padding:5px 0;">Rs. {{ number_format((float) $order->discount_amount, 2) }}</td>
    </tr>
    <tr>
        <td style="padding:10px 0;font-weight:700;border-top:1px solid #ece5d8;">Final total</td>
        <td align="right" style="padding:10px 0;font-weight:700;border-top:1px solid #ece5d8;">Rs. {{ number_format((float) $order->total_amount, 2) }}</td>
    </tr>
</table>
