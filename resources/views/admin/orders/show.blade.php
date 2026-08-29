<x-admin.layout title="Order {{ $order->order_number }}">
    <main class="mx-auto w-full max-w-[92rem] px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb & Back --}}
        <div class="mb-4 flex items-center gap-2 text-xs font-semibold text-zinc-500">
            <a href="{{ route('admin.index') }}" class="inline-flex items-center gap-1 text-zinc-600 hover:text-red-700 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Dashboard
            </a>
            <span>/</span>
            <a href="{{ route('admin.orders.index') }}" class="text-zinc-600 hover:text-red-700 transition">
                Orders
            </a>
            <span>/</span>
            <span class="text-zinc-900">{{ $order->order_number }}</span>
        </div>

        {{-- Order Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-200 pb-6">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">
                        {{ $order->order_number }}
                    </h1>
                    @if($order->status === 'completed')
                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Completed</span>
                    @elseif($order->status === 'processing')
                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-600/20">Processing</span>
                    @elseif($order->status === 'pending')
                        <span class="inline-flex items-center rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">Pending</span>
                    @else
                        <span class="inline-flex items-center rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-600">{{ ucfirst($order->status) }}</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-zinc-500">
                    Placed on {{ $order->created_at->format('F j, Y \a\t h:i A') }} ({{ $order->created_at->diffForHumans() }})
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.orders.receipt.download', $order) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-zinc-800">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Download Invoice (PDF)
                </a>
                <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                    ← Back to All Orders
                </a>
            </div>
        </div>

        {{-- Order Grid --}}
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Order Items Table (2 cols) --}}
            <div class="lg:col-span-2 rounded-xl border border-zinc-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-zinc-200 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-base font-bold text-zinc-950">Ordered Items ({{ $order->items->count() }})</h2>
                    <span class="text-xs font-semibold text-zinc-500">Itemized line records</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200">
                        <thead>
                            <tr class="bg-zinc-50/75 text-left text-xs font-semibold text-zinc-500">
                                <th class="py-3.5 pl-6 pr-3">Product</th>
                                <th class="px-3 py-3.5 text-right">Unit Price</th>
                                <th class="px-3 py-3.5 text-center">Qty</th>
                                <th class="py-3.5 pl-3 pr-6 text-right">Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($order->items as $item)
                                <tr class="transition hover:bg-zinc-50/60">
                                    <td class="py-4 pl-6 pr-3">
                                        <div class="flex items-center gap-3">
                                            @if($item->product && $item->product->image_path)
                                                <img class="h-12 w-12 shrink-0 rounded-lg object-cover ring-1 ring-zinc-200" src="{{ asset($item->product->image_path) }}" alt="{{ $item->product->name }}">
                                            @else
                                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-zinc-100 ring-1 ring-zinc-200">
                                                    <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                @if($item->product)
                                                    <a href="{{ route('admin.analytics.products.show', $item->product) }}" class="font-semibold text-sm text-zinc-900 hover:text-red-700 transition">
                                                        {{ $item->product->name }}
                                                    </a>
                                                    <p class="text-xs text-zinc-400">SKU: {{ $item->product->sku ?? '—' }} · {{ $item->product->category }}</p>
                                                @else
                                                    <p class="font-semibold text-sm text-zinc-900">Product #{{ $item->product_id }} (Archived)</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-right text-sm text-zinc-700">
                                        ₹{{ number_format($item->unit_price, 0) }}
                                    </td>
                                    <td class="px-3 py-4 text-center text-sm font-semibold text-zinc-900">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="py-4 pl-3 pr-6 text-right text-sm font-bold text-zinc-950">
                                        ₹{{ number_format($item->total_price, 0) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Total Calculation footer --}}
                <div class="border-t border-zinc-200 bg-zinc-50/60 p-6">
                    <div class="flex justify-end">
                        <div class="w-full max-w-xs space-y-2 text-sm">
                            <div class="flex justify-between text-zinc-600">
                                <span>Subtotal</span>
                                <span class="font-medium text-zinc-900">₹{{ number_format($order->total_amount, 0) }}</span>
                            </div>
                            <div class="flex justify-between text-zinc-600">
                                <span>Shipping (Complimentary)</span>
                                <span class="font-semibold text-emerald-700">FREE</span>
                            </div>
                            <div class="flex justify-between border-t border-zinc-200 pt-2 text-base font-bold text-zinc-950">
                                <span>Total Paid</span>
                                <span class="text-red-700">₹{{ number_format($order->total_amount, 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Customer & Payment Info (1 col) --}}
            <div class="space-y-6">
                {{-- Customer Card --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-bold text-zinc-950">Customer Details</h2>
                    <div class="mt-4 flex items-center gap-3 border-b border-zinc-100 pb-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-900 text-sm font-bold text-white">
                            {{ strtoupper(substr($order->user?->name ?? 'C', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-zinc-900">{{ $order->user?->name ?? 'Guest / Deleted User' }}</p>
                            <p class="text-xs text-zinc-500">{{ $order->user?->email ?? '—' }}</p>
                        </div>
                    </div>

                    <dl class="mt-4 space-y-3 text-xs">
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Customer ID</dt>
                            <dd class="font-mono font-semibold text-zinc-800">#{{ $order->user_id ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Member Since</dt>
                            <dd class="font-semibold text-zinc-800">{{ $order->user?->created_at ? $order->user->created_at->format('M Y') : '—' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Payment & Fulfillment Card --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-bold text-zinc-950">Payment & Status</h2>

                    <dl class="mt-4 space-y-3.5 text-xs">
                        <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
                            <dt class="text-zinc-500">Payment Status</dt>
                            <dd>
                                @if($order->payment_status === 'paid')
                                    <span class="rounded-md bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700">Paid in Full</span>
                                @elseif($order->payment_status === 'pending')
                                    <span class="rounded-md bg-amber-50 px-2 py-1 text-xs font-bold text-amber-700">Payment Pending</span>
                                @else
                                    <span class="rounded-md bg-rose-50 px-2 py-1 text-xs font-bold text-rose-700">{{ ucfirst($order->payment_status) }}</span>
                                @endif
                            </dd>
                        </div>

                        <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
                            <dt class="text-zinc-500">Fulfillment Status</dt>
                            <dd>
                                @if($order->status === 'completed' || $order->status === 'Delivered')
                                    <span class="rounded-md bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700">Delivered</span>
                                @elseif($order->status === 'processing' || $order->status === 'Shipped' || $order->status === 'Out for Delivery')
                                    <span class="rounded-md bg-blue-50 px-2 py-1 text-xs font-bold text-blue-700">{{ $order->status }}</span>
                                @else
                                    <span class="rounded-md bg-zinc-100 px-2 py-1 text-xs font-bold text-zinc-700">{{ $order->status }}</span>
                                @endif
                            </dd>
                        </div>

                        <div class="flex items-center justify-between">
                            <dt class="text-zinc-500">Payment Method</dt>
                            <dd class="font-semibold text-zinc-900">{{ strtoupper($order->payment_method ?? 'COD') }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Update Status Card --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-bold text-zinc-950">Update Order Status</h2>
                    
                    @php
                        $hasAcceptedReturn = $order->returnRequest && $order->returnRequest->status === 'Approved';
                        $hasAcceptedRefund = $order->refundRequest && $order->refundRequest->status === 'Completed';
                        $isStatusLocked = $hasAcceptedReturn || $hasAcceptedRefund;
                    @endphp

                    @if($isStatusLocked)
                        <div class="mt-4 rounded-xl bg-amber-50 p-4 border border-amber-100 text-xs text-amber-800 space-y-1">
                            <p class="font-bold">Status Locked</p>
                            <p>This order status is locked due to an accepted Return or Refund request.</p>
                        </div>
                    @elseif($order->status === 'Cancelled')
                        <div class="mt-4 rounded-xl bg-red-50 p-4 border border-red-100 text-xs text-red-700 space-y-1">
                            <p class="font-bold">This order is Cancelled.</p>
                            <p>Reason: {{ $order->cancellation_reason }}</p>
                            <p>Cancelled at: {{ $order->cancelled_at?->format('M d, Y H:i') }}</p>
                        </div>
                    @elseif($order->status === 'Pending')
                        <div class="mt-4 space-y-3">
                            <div class="rounded-xl bg-amber-50/70 p-3 border border-amber-100 text-xs text-amber-800">
                                <p class="font-bold">Order is Pending Admin Acceptance.</p>
                            </div>
                            
                            {{-- Accept Order Form --}}
                            <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Confirmed">
                                <button type="submit" class="w-full rounded-lg bg-emerald-600 hover:bg-emerald-700 py-2.5 text-xs font-semibold text-white shadow-sm transition">
                                    Accept Order
                                </button>
                            </form>

                            {{-- Cancel Order Form --}}
                            <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}" class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Cancelled">
                                <input type="text" name="cancellation_reason" placeholder="Cancellation reason..." class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-xs text-zinc-950 focus:border-zinc-900 focus:outline-none" required>
                                <button type="submit" class="w-full rounded-lg bg-red-600 hover:bg-red-700 py-2.5 text-xs font-semibold text-white shadow-sm transition">
                                    Cancel Order
                                </button>
                            </form>
                        </div>
                    @else
                        <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}" class="mt-4 space-y-4">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label for="status" class="block text-xs font-semibold text-zinc-500 uppercase tracking-wider">Select Status</label>
                                <select id="status" name="status" class="mt-2 block w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-950 focus:border-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900">
                                    <option value="Confirmed" {{ $order->status === 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="Shipped" {{ $order->status === 'Shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="Out for Delivery" {{ $order->status === 'Out for Delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                    <option value="Delivered" {{ $order->status === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="Cancelled" {{ $order->status === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full rounded-lg bg-zinc-950 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-zinc-800">
                                Save Changes
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Return Request Card --}}
                @if($order->returnRequest)
                    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
                        <h2 class="text-base font-bold text-zinc-950">Return Request Details</h2>
                        <dl class="mt-3 space-y-2 text-xs">
                            <div class="flex justify-between">
                                <dt class="text-zinc-500">Status</dt>
                                <dd class="font-bold text-zinc-900">{{ $order->returnRequest->status }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-zinc-500">Reason</dt>
                                <dd class="font-medium text-zinc-900 text-right">{{ $order->returnRequest->reason }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-zinc-500">Submitted</dt>
                                <dd class="font-medium text-zinc-900">{{ $order->returnRequest->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                        </dl>
                        @if($order->returnRequest->status === 'Pending')
                            <div class="mt-4 flex gap-2">
                                <form method="POST" action="{{ route('admin.returnRequests.updateStatus', $order->returnRequest) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Approved">
                                    <button type="submit" class="w-full rounded-lg bg-emerald-600 hover:bg-emerald-700 py-2 text-xs font-semibold text-white transition">
                                        Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.returnRequests.updateStatus', $order->returnRequest) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Rejected">
                                    <button type="submit" class="w-full rounded-lg bg-red-600 hover:bg-red-700 py-2 text-xs font-semibold text-white transition">
                                        Deny
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Refund Request Card --}}
                @if($order->refundRequest)
                    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
                        <h2 class="text-base font-bold text-zinc-950">Refund Request Details</h2>
                        <dl class="mt-3 space-y-2 text-xs">
                            <div class="flex justify-between">
                                <dt class="text-zinc-500">Status</dt>
                                <dd class="font-bold text-zinc-900">{{ $order->refundRequest->status }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-zinc-500">Amount</dt>
                                <dd class="font-bold text-zinc-900">Rs. {{ number_format($order->refundRequest->amount, 2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-zinc-500">Reason</dt>
                                <dd class="font-medium text-zinc-900 text-right">{{ $order->refundRequest->reason }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-zinc-500">Submitted</dt>
                                <dd class="font-medium text-zinc-900">{{ $order->refundRequest->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                        </dl>
                        @if($order->refundRequest->status === 'Pending')
                            <div class="mt-4 flex gap-2">
                                <form method="POST" action="{{ route('admin.refundRequests.updateStatus', $order->refundRequest) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Completed">
                                    <button type="submit" class="w-full rounded-lg bg-emerald-600 hover:bg-emerald-700 py-2 text-xs font-semibold text-white transition">
                                        Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.refundRequests.updateStatus', $order->refundRequest) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Rejected">
                                    <button type="submit" class="w-full rounded-lg bg-red-600 hover:bg-red-700 py-2 text-xs font-semibold text-white transition">
                                        Deny
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </main>
</x-admin.layout>

