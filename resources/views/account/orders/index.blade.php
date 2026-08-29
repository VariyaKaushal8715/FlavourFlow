<x-site.layout :site="$site" page-title="My Orders | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    <section class="border-b border-amber-200/60 bg-[radial-gradient(circle_at_top_left,rgba(244,185,66,0.24),transparent_34%),linear-gradient(135deg,#fff9ed_0%,#fff_52%,#fff3df_100%)] py-10 sm:py-14">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-6" data-reveal>
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold text-brand-primary">Purchase History</p>
                    <h1 class="mt-2 text-3xl font-semibold text-zinc-950 sm:text-4xl">My Orders</h1>
                    <p class="mt-4 text-base leading-7 text-zinc-600">Track and manage your past spice selections and delivery states.</p>
                </div>

                {{-- Notifications --}}
                <div class="relative">
                    @php
                        $unreadCount = $notifications->whereNull('read_at')->count();
                    @endphp
                    <button id="cust-notif-bell" class="relative rounded-full border border-amber-200/80 bg-white p-3 text-zinc-700 shadow-sm transition hover:bg-amber-50/50 hover:text-zinc-900 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        @if($unreadCount > 0)
                            <span class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white ring-2 ring-white">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </button>

                    <div id="cust-notif-dropdown" class="absolute right-0 mt-2 hidden w-80 rounded-3xl border border-amber-200 bg-white p-4 shadow-2xl z-50">
                        <div class="flex justify-between items-center border-b border-zinc-100 pb-3">
                            <span class="text-sm font-bold text-zinc-950">Notifications</span>
                            <span class="text-[10px] text-zinc-500 font-semibold">{{ $unreadCount }} unread</span>
                        </div>
                        <div class="mt-3 max-h-64 overflow-y-auto divide-y divide-zinc-50">
                            @if($notifications->isEmpty())
                                <p class="text-xs text-zinc-500 text-center py-6">No notifications yet.</p>
                            @else
                                @foreach($notifications as $notif)
                                    @php
                                        $isUnread = is_null($notif->read_at);
                                    @endphp
                                    <a href="{{ route('account.orders.show', $notif->order->order_number) }}" class="block py-2.5 hover:bg-amber-50/20 transition rounded-xl px-2 {{ $isUnread ? 'bg-amber-50/10 font-medium' : '' }}">
                                        <div class="flex justify-between items-start gap-2">
                                            <p class="text-xs text-zinc-800 leading-tight">
                                                @if($isUnread)
                                                    <span class="inline-block h-2 w-2 rounded-full bg-red-500 mr-1"></span>
                                                @endif
                                                {{ $notif->message }}
                                            </p>
                                        </div>
                                        <p class="mt-1 text-[10px] text-zinc-400">
                                            {{ $notif->created_at->diffForHumans() }}
                                        </p>
                                    </a>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const bell = document.getElementById('cust-notif-bell');
            const dropdown = document.getElementById('cust-notif-dropdown');
            
            if (bell && dropdown) {
                bell.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dropdown.classList.toggle('hidden');
                });
                
                document.addEventListener('click', (e) => {
                    if (!dropdown.contains(e.target) && !bell.contains(e.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            }

            // Real-time SSE Notifications
            const sseUrl = "{{ route('account.orders.notifications.sse') }}";
            const eventSource = new EventSource(sseUrl);

            eventSource.onmessage = function(event) {
                const data = JSON.parse(event.data);
                if (!data.id) return; // Skip connection heartbeats

                // 1. Play sound or show popup/toast
                showToast(data.message);

                // 2. Update badge count
                let badge = bell.querySelector('span');
                if (data.unread_count > 0) {
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = "absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white ring-2 ring-white";
                        bell.appendChild(badge);
                    }
                    badge.textContent = data.unread_count;
                } else if (badge) {
                    badge.remove();
                }

                // 3. Prepend notification to list
                const container = dropdown.querySelector('.overflow-y-auto');
                
                // Clear "No notifications yet" message if it exists
                const emptyMsg = container.querySelector('.py-6');
                if (emptyMsg) emptyMsg.remove();

                const newNotifHtml = `
                    <a href="${data.url}" class="block py-2.5 hover:bg-amber-50/20 transition rounded-xl px-2 bg-amber-50/10 font-medium">
                        <div class="flex justify-between items-start gap-2">
                            <p class="text-xs text-zinc-800 leading-tight">
                                <span class="inline-block h-2 w-2 rounded-full bg-red-500 mr-1"></span>
                                ${data.message}
                            </p>
                        </div>
                        <p class="mt-1 text-[10px] text-zinc-400">
                            ${data.created_at}
                        </p>
                    </a>
                `;
                container.insertAdjacentHTML('afterbegin', newNotifHtml);

                // Update unread count inside the header
                const headerUnread = dropdown.querySelector('.text-zinc-500');
                if (headerUnread) {
                    headerUnread.textContent = `${data.unread_count} unread`;
                }
            };

            function showToast(message) {
                const toast = document.createElement('div');
                toast.className = "fixed bottom-5 right-5 z-50 rounded-2xl bg-zinc-950 border border-amber-500/30 p-4 text-xs font-semibold text-white shadow-2xl transition-all duration-300 transform translate-y-10 opacity-0 max-w-sm";
                toast.innerHTML = `
                    <div class="flex items-center gap-3">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-primary text-white">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-zinc-300 text-[10px] font-bold uppercase tracking-wider">New Notification</p>
                            <p class="mt-0.5 text-white leading-normal">${message}</p>
                        </div>
                    </div>
                `;
                document.body.appendChild(toast);

                // Animation
                setTimeout(() => {
                    toast.classList.remove('translate-y-10', 'opacity-0');
                }, 100);

                // Remove after 6 seconds
                setTimeout(() => {
                    toast.classList.add('translate-y-10', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 6000);
            }
        });
    </script>

    <section class="bg-[linear-gradient(180deg,#fff_0%,#fff9ed_48%,#fff_100%)] py-12 sm:py-16">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            @if($orders->isEmpty())
                <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-12 text-center shadow-[0_24px_70px_rgba(120,53,15,0.06)]" data-reveal>
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-brand-primary">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <h2 class="mt-4 text-xl font-semibold text-zinc-950">No Orders Found</h2>
                    <p class="mt-2 text-sm text-zinc-600">You haven't placed any spice orders yet. Start exploring our rich collection!</p>
                    <a href="{{ route('home') }}#products" class="mt-6 inline-flex rounded-2xl bg-zinc-950 px-6 py-3 text-sm font-semibold text-white transition hover:bg-brand-primary">
                        Browse Spices
                    </a>
                </div>
            @else
                <div class="space-y-6" data-reveal>
                    @foreach($orders as $order)
                        <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)] transition hover:border-amber-300">
                            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-100 pb-4">
                                <div>
                                    <span class="text-xs font-semibold text-zinc-400">Order ID</span>
                                    <p class="font-bold text-zinc-950 tracking-wide text-sm sm:text-base">{{ $order->order_number }}</p>
                                </div>
                                <div>
                                    <span class="text-xs font-semibold text-zinc-400">Date</span>
                                    <p class="font-semibold text-zinc-950 text-sm">{{ $order->created_at->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-xs font-semibold text-zinc-400">Total</span>
                                    <p class="font-bold text-brand-primary text-sm">Rs. {{ number_format($order->total_amount, 2) }}</p>
                                </div>
                                <div>
                                    <span class="text-xs font-semibold text-zinc-400">Status</span>
                                    <p class="mt-0.5">
                                        <span @class([
                                            'rounded-full px-2.5 py-0.5 text-xs font-semibold shadow-sm',
                                            'bg-emerald-100 text-emerald-800' => $order->status === 'Confirmed' || $order->status === 'Delivered',
                                            'bg-amber-100 text-amber-800' => $order->status === 'Shipped' || $order->status === 'Out for Delivery',
                                        ])>
                                            {{ $order->status }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                                <p class="text-xs text-zinc-500">
                                    Contains <span class="font-semibold text-zinc-700">{{ $order->items_count }} item(s)</span> | Paid via <span class="font-semibold text-zinc-700">{{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Online Payment' }}</span>
                                </p>

                                <div class="flex items-center gap-2">
                                    <a
                                        href="{{ route('account.orders.show', $order->order_number) }}"
                                        class="rounded-xl border border-zinc-300 bg-white px-4 py-2 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50"
                                    >
                                        View Details
                                    </a>
                                    <a
                                        href="{{ route('account.orders.track', $order->order_number) }}"
                                        class="rounded-xl bg-zinc-950 px-4 py-2 text-xs font-semibold text-white transition hover:bg-brand-primary"
                                    >
                                        Track Order
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-site.layout>

