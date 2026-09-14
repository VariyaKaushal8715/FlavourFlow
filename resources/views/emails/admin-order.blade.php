@include('emails.partials.header', ['title' => $headline])
    <h1 style="font-size:24px;line-height:1.3;margin:0 0 12px;">{{ $headline }}</h1>
    <p style="font-size:15px;line-height:1.7;margin:0 0 18px;">{{ $intro }}</p>

    @if ($user && ! $order)
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:18px 0;">
            <tr>
                <td style="padding:6px 0;color:#555;width:42%;">Customer</td>
                <td style="padding:6px 0;">{{ $user->name }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#555;">Email</td>
                <td style="padding:6px 0;">{{ $user->email }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#555;">Registered</td>
                <td style="padding:6px 0;">{{ $user->created_at?->format('d M Y, h:i A') }}</td>
            </tr>
        </table>
    @endif

    @if ($order)
        @include('emails.partials.order-details', ['order' => $order, 'payment' => $payment])
        @include('emails.partials.order-summary', ['order' => $order])

        <p style="margin:22px 0 0;">
            <a href="{{ route('admin.orders.show', $order) }}" style="display:inline-block;background:#21412a;color:#ffffff;text-decoration:none;border-radius:6px;padding:11px 16px;font-weight:700;">
                View admin order
            </a>
        </p>
    @endif
@include('emails.partials.footer')
