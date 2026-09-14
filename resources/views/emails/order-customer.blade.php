@include('emails.partials.header', ['title' => $headline])
    <h1 style="font-size:24px;line-height:1.3;margin:0 0 12px;">{{ $headline }}</h1>
    <p style="font-size:15px;line-height:1.7;margin:0 0 18px;">Hi {{ $order->name }}, {{ $intro }}</p>

    @include('emails.partials.order-details', ['order' => $order])
    @include('emails.partials.order-summary', ['order' => $order])

    <p style="margin:22px 0 0;">
        <a href="{{ route('account.orders.show', $order) }}" style="display:inline-block;background:#21412a;color:#ffffff;text-decoration:none;border-radius:6px;padding:11px 16px;font-weight:700;">
            View order
        </a>
    </p>
@include('emails.partials.footer')
