<x-site.layout :site="$site" page-title="Order Details #{{ $order->order_number }} | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    {{-- Top Banner / Header --}}
    <section class="border-b border-amber-200/60 bg-[radial-gradient(circle_at_top_left,rgba(244,185,66,0.24),transparent_34%),linear-gradient(135deg,#fff9ed_0%,#fff_52%,#fff3df_100%)] py-8 sm:py-12">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between" data-reveal>
                <div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('account.orders') }}" class="inline-flex items-center text-xs font-semibold text-brand-primary hover:underline gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            Back to My Orders
                        </a>
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-bold text-zinc-950 sm:text-3xl">Order #{{ $order->order_number }}</h1>
                        @php
                            $statusClasses = match($order->status) {
                                'Delivered' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'Cancelled' => 'bg-red-100 text-red-800 border-red-200',
                                'Out for Delivery', 'Shipped' => 'bg-amber-100 text-amber-800 border-amber-200',
                                default => 'bg-blue-100 text-blue-800 border-blue-200',
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-full border px-3 py-0.5 text-xs font-semibold {{ $statusClasses }}">
                            {{ $order->status }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-zinc-600 sm:text-sm">Placed on {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>

                {{-- Header Actions for Quick Access --}}
                <div class="flex flex-wrap items-center gap-2 pt-2 md:pt-0">
                    <a href="{{ route('account.orders.track', $order->order_number) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-zinc-950 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-primary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 18.782V8.018a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        Track Order
                    </a>
                    <a href="{{ route('account.orders.receipt', $order->order_number) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs font-semibold text-zinc-900 shadow-sm transition hover:bg-zinc-100">
                        <svg class="w-4 h-4 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download Receipt
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Content Section --}}
    <section class="bg-[linear-gradient(180deg,#fff_0%,#fff9ed_48%,#fff_100%)] py-8 sm:py-12">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-3 items-start" data-reveal>
                
                {{-- Left Main Column (Span 2) --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Card 1: Ordered Items --}}
                    <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)] sm:p-8">
                        <div class="flex items-center justify-between border-b border-zinc-100 pb-4">
                            <h2 class="text-lg font-bold text-zinc-950">Ordered Items</h2>
                            <span class="rounded-full bg-amber-100/80 px-2.5 py-0.5 text-xs font-semibold text-amber-900">
                                {{ count($order->items) }} {{ Str::plural('item', count($order->items)) }}
                            </span>
                        </div>

                        <div class="mt-6 divide-y divide-zinc-100">
                            @foreach ($order->items as $item)
                                <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row items-start gap-4">
                                    {{-- Product Image Container --}}
                                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-zinc-50 border border-zinc-200/70 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                        @if($item->product && $item->product->image_path)
                                            <a href="{{ route('products.show', $item->product->slug) }}" class="block w-full h-full">
                                                <img class="w-full h-full object-cover transition-transform duration-300 hover:scale-105" src="{{ asset($item->product->image_path) }}" alt="{{ $item->product_name }}" />
                                            </a>
                                        @else
                                            <svg class="w-8 h-8 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        @endif
                                    </div>

                                    {{-- Product Details --}}
                                    <div class="flex-1 min-w-0 space-y-1.5 w-full">
                                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-1">
                                            @if($item->product)
                                                <a href="{{ route('products.show', $item->product->slug) }}" class="font-bold text-sm text-zinc-950 hover:text-brand-primary transition truncate">
                                                    {{ $item->product_name }}
                                                </a>
                                            @else
                                                <span class="font-bold text-sm text-zinc-950">{{ $item->product_name }}</span>
                                            @endif
                                            <span class="font-extrabold text-sm text-zinc-950 sm:text-right">
                                                Rs. {{ number_format($item->total_price, 2) }}
                                            </span>
                                        </div>

                                        {{-- Badges / Tags --}}
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                                            @if(isset($item->product->category))
                                                <span class="inline-flex items-center rounded-md bg-zinc-100 px-2 py-0.5 text-[11px] font-medium text-zinc-700">
                                                    {{ $item->product->category }}
                                                </span>
                                            @endif
                                            @if(isset($item->product->sku))
                                                <span class="text-[11px] text-zinc-400">SKU: {{ $item->product->sku }}</span>
                                            @endif
                                            @if(isset($item->product->unit))
                                                <span class="text-[11px] text-zinc-400">&bull; Pack: {{ $item->product->unit }}</span>
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                                            <div class="text-xs text-zinc-600">
                                                <span class="font-semibold text-zinc-800">Qty:</span> {{ $item->quantity }} {{ $item->unit }}
                                                <span class="mx-1 text-zinc-300">|</span>
                                                <span class="font-semibold text-zinc-800">Price:</span> Rs. {{ number_format($item->unit_price, 2) }}
                                            </div>

                                            <div class="flex items-center gap-3">
                                                @if(isset($item->review))
                                                    <div class="flex items-center text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-200/60">
                                                        <svg class="w-3.5 h-3.5 fill-current mr-1" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0.49 6.91l6.562-.954L10 0l2.948 5.956 6.562.954-4.755 4.635 1.123 6.545z"/></svg>
                                                        {{ $item->review->rating }}/5 Rated
                                                    </div>
                                                @elseif($order->status === 'Delivered')
                                                    <a href="{{ route('reviews.pending') }}" class="text-xs font-semibold text-amber-700 hover:text-amber-800 underline">
                                                        Leave Review
                                                    </a>
                                                @endif

                                                @if($item->product)
                                                    <a href="{{ route('products.show', $item->product->slug) }}" class="inline-flex items-center text-xs font-medium text-zinc-600 hover:text-zinc-950 transition">
                                                        View Product &rarr;
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Card 2: Delivery & Shipping Information --}}
                    <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)] sm:p-8">
                        <h2 class="text-lg font-bold text-zinc-950 border-b border-zinc-100 pb-4">Delivery & Payment Info</h2>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2">
                            {{-- Delivery Address --}}
                            <div class="space-y-2 text-xs sm:text-sm text-zinc-700 bg-zinc-50/60 p-4 rounded-2xl border border-zinc-100">
                                <div class="flex items-center gap-1.5 font-bold text-zinc-950 text-sm">
                                    <svg class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Delivery Destination
                                </div>
                                <p class="font-semibold text-zinc-900 pt-1">{{ $order->name }}</p>
                                <p>{{ $order->address }}</p>
                                <p>{{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}</p>
                                <p>{{ $order->country }}</p>
                                <p class="pt-2 text-xs font-medium text-zinc-500 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $order->mobile }}
                                </p>
                            </div>

                            {{-- Payment & Shipping Method --}}
                            <div class="space-y-2 text-xs sm:text-sm text-zinc-700 bg-zinc-50/60 p-4 rounded-2xl border border-zinc-100">
                                <div class="flex items-center gap-1.5 font-bold text-zinc-950 text-sm">
                                    <svg class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    Payment & Shipping Details
                                </div>
                                <div class="pt-1 space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500">Payment Method:</span>
                                        <span class="font-semibold text-zinc-900">{{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Online Payment' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500">Delivery Option:</span>
                                        <span class="font-semibold text-zinc-900">{{ ucfirst($order->delivery_option ?? 'standard') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500">Delivery Timeframe:</span>
                                        <span class="font-semibold text-zinc-900">{{ $order->delivery_days ?? '4-5 days' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Right Sidebar Column (Span 1) --}}
                <aside class="space-y-6">
                    @php
                        $canCancel = $order->status === 'Confirmed';
                        
                        $isDelivered = $order->status === 'Delivered';
                        $deliveredAt = $order->delivered_at;
                        $daysSinceDelivery = $deliveredAt ? (int) $deliveredAt->diffInDays(now()) : null;
                        $isWithin7Days = $deliveredAt && $daysSinceDelivery <= 7;
                        
                        $hasReturn = $order->returnRequest()->exists();
                        $hasRefund = $order->refundRequest()->exists();
                        
                        $canReturn = $isDelivered && $isWithin7Days && !$hasReturn && !$hasRefund;
                        $canRefund = $isDelivered && $isWithin7Days && !$hasReturn && !$hasRefund;
                        
                        $returnRequest = $order->returnRequest;
                        $refundRequest = $order->refundRequest;
                    @endphp

                    {{-- Unified Order Summary & Actions Card --}}
                    <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)]">
                        <h2 class="text-lg font-bold text-zinc-950 border-b border-zinc-100 pb-4">Order Summary</h2>

                        {{-- Price Breakdown --}}
                        <div class="mt-4 space-y-3 text-xs sm:text-sm">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Subtotal</span>
                                <span class="font-semibold text-zinc-900">Rs. {{ number_format($order->subtotal, 2) }}</span>
                            </div>

                            @if($order->discount_amount > 0)
                                <div class="flex justify-between text-emerald-700">
                                    <span class="flex items-center gap-1 font-medium">
                                        Discount
                                        @if($order->coupon_code)
                                            <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold text-emerald-800">{{ $order->coupon_code }}</span>
                                        @endif
                                    </span>
                                    <span class="font-bold">- Rs. {{ number_format($order->discount_amount, 2) }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between">
                                <span class="text-zinc-500">Delivery Charge</span>
                                <span class="font-semibold text-zinc-900">
                                    {{ $order->delivery_charge > 0 ? 'Rs. '.number_format($order->delivery_charge, 2) : 'FREE' }}
                                </span>
                            </div>

                            <div class="flex justify-between border-t border-zinc-100 pt-3 text-base font-extrabold text-zinc-950">
                                <span>Total Amount</span>
                                <span class="text-brand-primary">Rs. {{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="mt-6 space-y-2.5">
                            <a href="{{ route('account.orders.track', $order->order_number) }}" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-zinc-950 py-3 text-center text-xs font-bold text-white transition hover:bg-brand-primary shadow-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 18.782V8.018a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                Track Order Progress
                            </a>

                            <a href="{{ route('account.orders.receipt', $order->order_number) }}" class="flex w-full items-center justify-center gap-2 rounded-2xl border border-zinc-300 bg-white py-3 text-center text-xs font-bold text-zinc-900 transition hover:bg-zinc-100 shadow-sm">
                                <svg class="w-4 h-4 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Download Receipt (PDF)
                            </a>
                        </div>

                        {{-- Manage Order Controls --}}
                        <div class="mt-6 border-t border-zinc-100 pt-6">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-400 mb-3">Manage Order</h3>
                            
                            <div class="space-y-3">
                                {{-- Cancel Button & Status --}}
                                @if($order->status === 'Cancelled')
                                    <div class="rounded-2xl bg-red-50 p-3.5 border border-red-100 text-xs text-red-700">
                                        <p class="font-bold">Order Cancelled</p>
                                        <p class="mt-1">Reason: {{ $order->cancellation_reason }}</p>
                                        <p class="mt-0.5 text-[11px] text-red-500">Cancelled on: {{ $order->cancelled_at?->format('M d, Y H:i') }}</p>
                                    </div>
                                @elseif($canCancel)
                                    <button onclick="openModal('cancel-order-modal')" class="w-full rounded-2xl bg-red-600 hover:bg-red-700 py-2.5 text-center text-xs font-semibold text-white transition shadow-sm">
                                        Cancel Order
                                    </button>
                                @endif

                                {{-- Return Request Banner / Button --}}
                                @if($returnRequest)
                                    <div class="rounded-2xl bg-amber-50 p-3.5 border border-amber-200/80 text-xs text-amber-900">
                                        <p class="font-bold">Return Requested ({{ $returnRequest->status }})</p>
                                        <p class="mt-1">Reason: {{ $returnRequest->reason }}</p>
                                        <p class="mt-0.5 text-[11px] text-amber-700">Requested on: {{ $returnRequest->created_at->format('M d, Y') }}</p>
                                    </div>
                                @elseif($isDelivered && $canReturn)
                                    <button onclick="openModal('return-order-modal')" class="w-full rounded-2xl border border-zinc-950 bg-white hover:bg-zinc-950 hover:text-white py-2.5 text-center text-xs font-semibold text-zinc-950 transition shadow-sm">
                                        Return Order
                                    </button>
                                @endif

                                {{-- Refund Request Banner / Button --}}
                                @if($refundRequest)
                                    <div class="rounded-2xl bg-emerald-50 p-3.5 border border-emerald-200/80 text-xs text-emerald-900">
                                        <p class="font-bold">Refund Requested ({{ $refundRequest->status }})</p>
                                        <p class="mt-1">Reason: {{ $refundRequest->reason }}</p>
                                        <p class="mt-1 font-bold text-emerald-800">Amount: Rs. {{ number_format($refundRequest->amount, 2) }}</p>
                                        <p class="mt-0.5 text-[11px] text-emerald-700">Requested on: {{ $refundRequest->created_at->format('M d, Y') }}</p>
                                    </div>
                                @elseif($isDelivered && $canRefund)
                                    <button onclick="openModal('refund-order-modal')" class="w-full rounded-2xl bg-zinc-950 hover:bg-zinc-800 py-2.5 text-center text-xs font-semibold text-white transition shadow-sm">
                                        Request Refund
                                    </button>
                                @endif

                                {{-- Return/Refund Expiry indicator --}}
                                @if($isDelivered)
                                    <div class="pt-1 text-center">
                                        @if($isWithin7Days)
                                            <p class="text-[11px] font-medium text-emerald-700">
                                                Return/Refund period expires in {{ 7 - $daysSinceDelivery }} day(s).
                                            </p>
                                        @else
                                            <p class="text-[11px] font-medium text-red-600">
                                                Return/Refund period has expired.
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </aside>

            </div>
        </div>
    </section>

    {{-- Modal Overlays --}}
    <div id="cancel-order-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-zinc-950/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl border border-amber-200 bg-white p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-zinc-950">Cancel Order</h3>
            <p class="mt-1 text-xs text-zinc-500">Please provide a reason for cancelling your order.</p>
            <form action="{{ route('account.orders.cancel', $order->order_number) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <textarea name="reason" rows="3" class="w-full rounded-2xl border border-amber-200/80 bg-amber-50/20 px-4 py-3 text-sm text-zinc-950 outline-none focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15" placeholder="Reason for cancellation..." required></textarea>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="closeModal('cancel-order-modal')" class="rounded-xl border border-zinc-200 px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50">Cancel</button>
                    <button type="submit" class="rounded-xl bg-red-600 hover:bg-red-700 px-4 py-2 text-xs font-semibold text-white shadow">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>

    <div id="return-order-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-zinc-950/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl border border-amber-200 bg-white p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-zinc-950">Return Order</h3>
            <p class="mt-1 text-xs text-zinc-500">Please provide a reason for returning this order.</p>
            <form action="{{ route('account.orders.return', $order->order_number) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <textarea name="reason" rows="3" class="w-full rounded-2xl border border-amber-200/80 bg-amber-50/20 px-4 py-3 text-sm text-zinc-950 outline-none focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15" placeholder="Reason for return..." required></textarea>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="closeModal('return-order-modal')" class="rounded-xl border border-zinc-200 px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50">Cancel</button>
                    <button type="submit" class="rounded-xl bg-zinc-950 hover:bg-zinc-800 px-4 py-2 text-xs font-semibold text-white shadow">Submit Return Request</button>
                </div>
            </form>
        </div>
    </div>

    <div id="refund-order-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-zinc-950/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl border border-amber-200 bg-white p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-zinc-950">Request Refund</h3>
            <p class="mt-1 text-xs text-zinc-500">Please state the reason for requesting a refund. The refund amount will be Rs. {{ number_format($order->total_amount, 2) }}.</p>
            <form action="{{ route('account.orders.refund', $order->order_number) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <textarea name="reason" rows="3" class="w-full rounded-2xl border border-amber-200/80 bg-amber-50/20 px-4 py-3 text-sm text-zinc-950 outline-none focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15" placeholder="Reason for refund..." required></textarea>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="closeModal('refund-order-modal')" class="rounded-xl border border-zinc-200 px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50">Cancel</button>
                    <button type="submit" class="rounded-xl bg-zinc-950 hover:bg-zinc-800 px-4 py-2 text-xs font-semibold text-white shadow">Submit Refund Request</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }
        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }
    </script>
</x-site.layout>


