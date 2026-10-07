<x-site.layout :site="$site" page-title="Order Details | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    <section class="border-b border-amber-200/60 bg-[radial-gradient(circle_at_top_left,rgba(244,185,66,0.24),transparent_34%),linear-gradient(135deg,#fff9ed_0%,#fff_52%,#fff3df_100%)] py-10 sm:py-14">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <div class="max-w-3xl" data-reveal>
                <div class="flex items-center gap-2">
                    <a href="{{ route('account.orders') }}" class="text-xs font-semibold text-brand-primary hover:underline">&larr; Back to My Orders</a>
                </div>
                <h1 class="mt-4 text-3xl font-semibold text-zinc-950 sm:text-4xl">Order Details</h1>
                <p class="mt-2 text-base leading-7 text-zinc-600">Review items, delivery destination, and tracking summary.</p>
            </div>
        </div>
    </section>

    <section class="bg-[linear-gradient(180deg,#fff_0%,#fff9ed_48%,#fff_100%)] py-12 sm:py-16">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[1fr_24rem] items-start" data-reveal>
                <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)] sm:p-8">
                    <h2 class="text-lg font-semibold text-zinc-950 border-b border-zinc-100 pb-4">Ordered Items</h2>

<div class="grid gap-4 md:grid-cols-1 lg:grid-cols-2">
    @foreach ($order->items as $item)
    <div class="border rounded-lg p-4 flex items-start bg-white">
    @if($item->product && $item->product->image_path)
        <a href="{{ route('products.show', $item->product->slug) }}" class="mr-4 flex-shrink-0">
            <img class="w-20 h-20 object-cover rounded" src="{{ asset($item->product->image_path) }}" alt="{{ $item->product_name }}" />
        </a>
    @endif
    <div class="flex-1">
        <a href="{{ route('products.show', $item->product->slug) }}" class="font-semibold text-sm text-zinc-950 hover:underline">{{ $item->product_name }}</a>
        @if(isset($item->product->category))
            <div class="text-xs text-gray-500">{{ $item->product->category }}</div>
        @endif
        @if(isset($item->product->sku))
            <div class="text-xs text-gray-500">SKU: {{ $item->product->sku }}</div>
        @endif
        @if(isset($item->product->unit))
            <div class="text-xs text-gray-500">Pack/Size: {{ $item->product->unit }}</div>
        @endif
        <div class="mt-1 text-xs">Qty: {{ $item->quantity }} {{ $item->unit }}</div>
        <div class="mt-1 text-xs">Unit Price: Rs. {{ number_format($item->unit_price, 2) }}</div>
        <div class="mt-1 font-medium text-sm">Total: Rs. {{ number_format($item->total_price, 2) }}</div>
        @if(isset($item->review))
            <div class="mt-1 flex items-center text-sm text-yellow-600">
                @for ($i = 0; $i < $item->review->rating; $i++)
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0.49 6.91l6.562-.954L10 0l2.948 5.956 6.562.954-4.755 4.635 1.123 6.545z"/></svg>
                @endfor
                <span class="ml-1">({{ $item->review->rating }}/5)</span>
            </div>
        @elseif($order->status === 'Delivered')
            <a href="{{ route('reviews.pending') }}" class="mt-2 inline-block text-sm text-blue-600 hover:underline">Leave Review</a>
        @endif
        <a href="{{ route('products.show', $item->product->slug) }}" class="mt-2 inline-block bg-brand-primary text-white text-xs px-3 py-1 rounded hover:bg-brand-primary/80">View Product</a>
    </div>
</div>
    @endforeach
</div>

<div class="mt-6 border-t pt-4">
    <h3 class="text-lg font-semibold mb-2">Order Summary</h3>
    <div class="flex justify-between"><span>Subtotal</span><span>Rs. {{ number_format($order->subtotal, 2) }}</span></div>
    @if($order->delivery_charge > 0)
        <div class="flex justify-between"><span>Delivery</span><span>Rs. {{ number_format($order->delivery_charge, 2) }}</span></div>
    @endif
    @if($order->discount_amount > 0)
        <div class="flex justify-between"><span>Discount ({{ $order->coupon_code }})</span><span>- Rs. {{ number_format($order->discount_amount, 2) }}</span></div>
    @endif
    <div class="flex justify-between font-bold mt-2"><span>Total</span><span>Rs. {{ number_format($order->total_amount, 2) }}</span></div>
    <div class="flex justify-between"><span>Payment Method</span><span>{{ $order->payment_method }}</span></div>
</div>
                </div>

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

                    <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)]">
                        <h2 class="text-lg font-semibold text-zinc-950 border-b border-zinc-100 pb-4">Manage Order</h2>
                        <div class="mt-4 space-y-3">
                            
                            {{-- Cancel Button --}}
                            @if($order->status === 'Cancelled')
                                <div class="rounded-2xl bg-red-50 p-4 border border-red-100 text-xs text-red-700">
                                    <p class="font-bold">This order has been cancelled.</p>
                                    <p class="mt-1">Reason: {{ $order->cancellation_reason }}</p>
                                    <p class="mt-0.5">Cancelled at: {{ $order->cancelled_at?->format('M d, Y H:i') }}</p>
                                </div>
                            @else
                                @if($canCancel)
                                    <button onclick="openModal('cancel-order-modal')" class="w-full rounded-2xl bg-red-600 hover:bg-red-700 py-3 text-center text-xs font-semibold text-white transition shadow-sm">
                                        Cancel Order
                                    </button>
                                @else
                                    <button class="w-full rounded-2xl bg-zinc-200 text-zinc-400 py-3 text-center text-xs font-semibold cursor-not-allowed" disabled>
                                        Cancel Order (Disabled)
                                    </button>
                                @endif
                            @endif

                            {{-- Return Request State/Button --}}
                            @if($returnRequest)
                                <div class="rounded-2xl bg-amber-50 p-4 border border-amber-100 text-xs text-amber-800">
                                    <p class="font-bold">Return Requested ({{ $returnRequest->status }})</p>
                                    <p class="mt-1">Reason: {{ $returnRequest->reason }}</p>
                                    <p class="mt-0.5">Requested on: {{ $returnRequest->created_at->format('M d, Y') }}</p>
                                </div>
                            @elseif($isDelivered)
                                @if($canReturn)
                                    <button onclick="openModal('return-order-modal')" class="w-full rounded-2xl border border-zinc-950 bg-white hover:bg-zinc-950 hover:text-white py-3 text-center text-xs font-semibold text-zinc-950 transition shadow-sm">
                                        Return Order
                                    </button>
                                @else
                                    <button class="w-full rounded-2xl bg-zinc-100 text-zinc-400 py-3 text-center text-xs font-semibold cursor-not-allowed" disabled>
                                        Return Order (Disabled)
                                    </button>
                                @endif
                            @endif

                            {{-- Refund Request State/Button --}}
                            @if($refundRequest)
                                <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 text-xs text-emerald-800">
                                    <p class="font-bold">Refund Requested ({{ $refundRequest->status }})</p>
                                    <p class="mt-1">Reason: {{ $refundRequest->reason }}</p>
                                    <p class="mt-1 font-semibold">Refund Amount: Rs. {{ number_format($refundRequest->amount, 2) }}</p>
                                    <p class="mt-0.5">Requested on: {{ $refundRequest->created_at->format('M d, Y') }}</p>
                                </div>
                            @elseif($isDelivered)
                                @if($canRefund)
                                    <button onclick="openModal('refund-order-modal')" class="w-full rounded-2xl bg-zinc-950 hover:bg-zinc-800 py-3 text-center text-xs font-semibold text-white transition shadow-sm">
                                        Request Refund
                                    </button>
                                @else
                                    <button class="w-full rounded-2xl bg-zinc-100 text-zinc-400 py-3 text-center text-xs font-semibold cursor-not-allowed" disabled>
                                        Request Refund (Disabled)
                                    </button>
                                @endif
                            @endif

                            {{-- Return/Refund Expiry and info --}}
                            @if($isDelivered)
                                <div class="mt-2 text-center">
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

                    <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)]">
                        <h2 class="text-lg font-semibold text-zinc-950 border-b border-zinc-100 pb-4">Order Info</h2>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Order ID:</span>
                                <span class="font-bold text-zinc-950">{{ $order->order_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Status:</span>
                                <span class="font-semibold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded text-xs">{{ $order->status }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Date:</span>
                                <span class="font-semibold text-zinc-950">{{ $order->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Payment:</span>
                                <span class="font-semibold text-zinc-950">{{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Online Payment' }}</span>
                            </div>
                        </div>
                        <a href="{{ route('account.orders.track', $order->order_number) }}" class="mt-5 block w-full rounded-2xl bg-zinc-950 py-3 text-center text-xs font-semibold text-white transition hover:bg-brand-primary">
                            Track Order Progress
                        </a>
                        <a href="{{ route('account.orders.receipt', $order->order_number) }}" class="mt-2 block w-full rounded-2xl border border-zinc-950 bg-white py-3 text-center text-xs font-semibold text-zinc-950 transition hover:bg-zinc-950 hover:text-white">
                            Download Receipt (PDF)
                        </a>
                    </div>

                    <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)]">
                        <h2 class="text-lg font-semibold text-zinc-950 border-b border-zinc-100 pb-4">Delivery Destination</h2>
                        <div class="mt-4 text-sm text-zinc-800 space-y-1">
                            <p class="font-semibold text-zinc-950">{{ $order->name }}</p>
                            <p>{{ $order->address }}</p>
                            <p>{{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}</p>
                            <p>{{ $order->country }}</p>
                            <p class="mt-2 text-xs text-zinc-500">Mobile: {{ $order->mobile }}</p>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)]">
                        <h2 class="text-lg font-semibold text-zinc-950 border-b border-zinc-100 pb-4">Order Summary</h2>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Subtotal:</span>
                                <span>Rs. {{ number_format($order->subtotal, 2) }}</span>
                            </div>
                            @if($order->discount_amount > 0)
                                <div class="flex justify-between text-emerald-700 font-semibold">
                                    <span>Discount{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}:</span>
                                    <span>-Rs. {{ number_format($order->discount_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Delivery Charge:</span>
                                <span>{{ $order->delivery_charge > 0 ? 'Rs. '.number_format($order->delivery_charge, 2) : 'FREE' }}</span>
                            </div>
                            <div class="flex justify-between border-t border-zinc-100 pt-3 text-base font-bold text-zinc-950">
                                <span>Total:</span>
                                <span>Rs. {{ number_format($order->total_amount, 2) }}</span>
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

