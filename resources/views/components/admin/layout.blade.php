@props(['title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ $title }} - FlavourFlow Admin</title>

        <link rel="icon" type="image/png" href="{{ asset('images/flavourflow-mark.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/flavourflow-mark.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-100 text-zinc-950 antialiased" data-admin-secure-page="true">
        @if (auth()->check() && auth()->user()->is_admin)
            <div class="flex min-h-screen flex-row bg-zinc-100">
                <div class="fixed inset-0 z-40 hidden bg-zinc-950/60 lg:hidden" data-admin-sidebar-overlay></div>

                <aside
                    class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col overflow-y-auto border-r border-zinc-800 bg-zinc-950 text-white shadow-2xl transition-transform duration-300 lg:sticky lg:top-0 lg:z-auto lg:h-screen lg:flex-none lg:translate-x-0 lg:shadow-none"
                    id="admin-sidebar"
                    data-admin-sidebar
                >
                    <div class="flex items-center gap-3 border-b border-white/10 px-6 py-5">
                        <img class="h-10 w-10 rounded-lg object-cover ring-1 ring-white/10" src="{{ asset('images/flavourflow-mark.png') }}" alt="FlavourFlow">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-white">FlavourFlow Admin</p>
                            <p class="truncate text-xs text-zinc-400">Operations & Analytics</p>
                        </div>
                    </div>

                    <nav class="flex-1 px-4 py-5" aria-label="Admin Navigation">
                        <div class="space-y-1.5">
                            <p class="px-4 text-[10px] font-bold uppercase tracking-wider text-zinc-400">Overview</p>

                            <a @class([
                                'flex items-center justify-between rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                                'bg-white text-zinc-950 shadow-sm' => request()->routeIs('admin.index'),
                                'text-zinc-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.index'),
                            ]) href="{{ route('admin.index') }}">
                                <div class="flex items-center gap-3">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                                    <span>Dashboard</span>
                                </div>
                            </a>

                            <a @class([
                                'flex items-center justify-between rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                                'bg-white text-zinc-950 shadow-sm' => request()->routeIs('admin.analytics*'),
                                'text-zinc-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.analytics*'),
                            ]) href="{{ route('admin.analytics.sales') }}">
                                <div class="flex items-center gap-3">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                                    <span>Sales Analytics</span>
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-red-400">Live</span>
                            </a>

                            <p class="pt-4 px-4 text-[10px] font-bold uppercase tracking-wider text-zinc-400">Operations</p>

                            <a @class([
                                'flex items-center justify-between rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                                'bg-white text-zinc-950 shadow-sm' => request()->routeIs('admin.orders*'),
                                'text-zinc-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.orders*'),
                            ]) href="{{ route('admin.orders.index') }}">
                                <div class="flex items-center gap-3">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                    <span>Orders</span>
                                </div>
                            </a>

                            <a @class([
                                'flex items-center justify-between rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                                'bg-white text-zinc-950 shadow-sm' => request()->routeIs('admin.products*'),
                                'text-zinc-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.products*'),
                            ]) href="{{ route('admin.products.index') }}">
                                <div class="flex items-center gap-3">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                                    <span>Products</span>
                                </div>
                            </a>

                            <a @class([
                                'flex items-center justify-between rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                                'bg-white text-zinc-950 shadow-sm' => request()->routeIs('admin.inventory*'),
                                'text-zinc-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.inventory*'),
                            ]) href="{{ route('admin.inventory.index') }}">
                                <div class="flex items-center gap-3">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" /></svg>
                                    <span>Inventory</span>
                                </div>
                            </a>

                            <a @class([
                                'flex items-center justify-between rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                                'bg-white text-zinc-950 shadow-sm' => request()->routeIs('admin.categories*'),
                                'text-zinc-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.categories*'),
                            ]) href="{{ route('admin.categories.index') }}">
                                <div class="flex items-center gap-3">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                                    <span>Categories</span>
                                </div>
                            </a>

                            <a @class([
                                'flex items-center justify-between rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                                'bg-white text-zinc-950 shadow-sm' => request()->routeIs('admin.offers*'),
                                'text-zinc-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.offers*'),
                            ]) href="{{ route('admin.offers.index') }}">
                                <div class="flex items-center gap-3">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span>Offers</span>
                                </div>
                            </a>

                            <p class="pt-4 px-4 text-[10px] font-bold uppercase tracking-wider text-zinc-400">Settings</p>

                            <a @class([
                                'flex items-center justify-between rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                                'bg-white text-zinc-950 shadow-sm' => request()->routeIs('admin.profile*'),
                                'text-zinc-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.profile*'),
                            ]) href="{{ route('admin.profile.edit') }}">
                                <div class="flex items-center gap-3">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                    <span>Admin Profile</span>
                                </div>
                            </a>
                        </div>
                    </nav>

                    <div class="border-t border-white/10 p-4">
                        @php
                            $adminUser = auth()->user();
                        @endphp

                        <a href="{{ route('admin.profile.edit') }}" class="mb-3 flex items-center gap-3 rounded-xl p-2 transition hover:bg-white/5 group">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white shadow-sm">
                                {{ strtoupper(substr($adminUser->name ?? 'A', 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-semibold text-white group-hover:text-red-400 transition">{{ $adminUser->name ?? 'Admin' }}</p>
                                <p class="truncate text-[11px] text-zinc-400">{{ $adminUser->email ?? '' }}</p>
                            </div>
                            <svg class="h-3.5 w-3.5 text-zinc-500 group-hover:text-zinc-300 transition" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                        </a>

                        <div class="flex gap-2">
                            <a href="{{ route('home') }}" target="_blank" class="flex flex-1 items-center justify-center rounded-lg border border-white/10 bg-white/5 py-2 text-xs font-semibold text-zinc-300 transition hover:bg-white/10 hover:text-white">
                                Storefront ↗
                            </a>
                            <form method="POST" action="{{ route('admin.logout') }}" class="flex-1">
                                @csrf
                                <button class="flex w-full items-center justify-center rounded-lg border border-white/10 bg-white/5 py-2 text-xs font-semibold text-zinc-300 transition hover:bg-red-950/40 hover:text-red-300" type="submit">
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </aside>

                <div class="min-w-0 flex-1">
                    <header class="sticky top-0 z-30 border-b border-zinc-200 bg-white/95 px-4 py-3 backdrop-blur lg:px-8">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <button
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-900 shadow-sm transition hover:border-zinc-300 lg:hidden"
                                    type="button"
                                    data-admin-sidebar-toggle
                                    aria-label="Open menu"
                                    aria-expanded="false"
                                    aria-controls="admin-sidebar"
                                >
                                    <span class="flex flex-col gap-1">
                                        <span class="h-0.5 w-4 rounded-full bg-current"></span>
                                        <span class="h-0.5 w-4 rounded-full bg-current"></span>
                                        <span class="h-0.5 w-4 rounded-full bg-current"></span>
                                    </span>
                                </button>
                                <span class="text-xs font-medium text-zinc-500 hidden sm:inline">FlavourFlow E-Commerce Operations</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <!-- Live Notification Dropdown -->
                                <div class="relative" id="admin-notification-container">
                                    <button type="button" id="notification-bell-btn" class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-600 shadow-sm transition hover:text-zinc-900 hover:border-zinc-300">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                        </svg>
                                        <span id="notification-badge" class="absolute -top-1.5 -right-1.5 hidden flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white ring-2 ring-white">0</span>
                                    </button>

                                    <!-- Dropdown menu -->
                                    <div id="notification-dropdown" class="absolute right-0 mt-2 hidden w-80 origin-top-right rounded-2xl border border-zinc-200 bg-white p-2 shadow-xl ring-1 ring-black/5 focus:outline-none z-50">
                                        <div class="border-b border-zinc-100 px-4 py-2.5 flex items-center justify-between">
                                            <span class="text-xs font-bold text-zinc-900">New Orders</span>
                                            <button type="button" id="mark-all-read-btn" class="text-[10px] font-bold text-brand-primary hover:underline">Clear</button>
                                        </div>
                                        <div class="max-h-64 overflow-y-auto py-1 divide-y divide-zinc-50" id="notification-items-list">
                                            <p class="text-xs text-zinc-500 text-center py-6">No new notifications</p>
                                        </div>
                                    </div>
                                </div>

                                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-zinc-800">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    Add Product
                                </a>

                                <a href="{{ route('admin.profile.edit') }}" class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 bg-white shadow-sm transition hover:border-zinc-300 hover:ring-2 hover:ring-red-500/20" title="Admin Profile">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-md bg-red-600 text-[11px] font-bold text-white">
                                        {{ strtoupper(substr($adminUser->name ?? 'A', 0, 1)) }}
                                    </div>
                                </a>
                            </div>
                        </div>
                    </header>
                    {{ $slot }}
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const bellBtn = document.getElementById('notification-bell-btn');
                    const dropdown = document.getElementById('notification-dropdown');
                    const badge = document.getElementById('notification-badge');
                    const listContainer = document.getElementById('notification-items-list');
                    const markReadBtn = document.getElementById('mark-all-read-btn');
                    
                    if (!bellBtn) return;

                    // Load read orders from localStorage to survive page refreshes
                    let readOrderIds = new Set(JSON.parse(localStorage.getItem('admin_read_order_ids') || '[]'));
                    let knownOrderIds = new Set();
                    let isFirstLoad = true;

                    const saveReadOrders = () => {
                        localStorage.setItem('admin_read_order_ids', JSON.stringify(Array.from(readOrderIds)));
                    };

                    const renderNotifications = (orders) => {
                        // Filter orders that have not been read yet
                        const unreadOrders = orders.filter(order => !readOrderIds.has(order.id));
                        
                        // Update badge
                        if (unreadOrders.length > 0) {
                            badge.textContent = unreadOrders.length;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }

                        if (orders.length === 0) {
                            listContainer.innerHTML = '<p class="text-xs text-zinc-500 text-center py-6">No orders found</p>';
                            return;
                        }

                        listContainer.innerHTML = '';
                        orders.forEach(order => {
                            const isUnread = !readOrderIds.has(order.id);
                            const item = document.createElement('a');
                            item.href = `/admin/orders/${order.order_number}`;
                            item.className = `flex flex-col gap-1 p-3 text-xs transition hover:bg-zinc-50 rounded-xl ${isUnread ? 'bg-zinc-50/80 border-l-2 border-red-500 font-semibold' : ''}`;
                            item.innerHTML = `
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-950 font-bold">${order.order_number}</span>
                                    <span class="text-zinc-400 text-[10px]">${new Date(order.created_at).toLocaleTimeString('en-IN', {hour: '2-digit', minute:'2-digit'})}</span>
                                </div>
                                <div class="flex justify-between items-center text-zinc-500">
                                    <span>${order.name || 'Customer'}</span>
                                    <span class="font-bold text-zinc-900">₹${parseFloat(order.total_amount).toFixed(0)}</span>
                                </div>
                            `;
                            
                            item.addEventListener('click', () => {
                                readOrderIds.add(order.id);
                                saveReadOrders();
                            });
                            
                            listContainer.appendChild(item);
                        });
                    };

                    const checkNewOrders = () => {
                        fetch('/admin/api/new-orders')
                            .then(res => res.json())
                            .then(data => {
                                if (data.orders) {
                                    let hasNew = false;
                                    
                                    data.orders.forEach(order => {
                                        // If it's a completely new order ID we haven't seen in this session
                                        if (!knownOrderIds.has(order.id)) {
                                            knownOrderIds.add(order.id);
                                            if (!isFirstLoad && !readOrderIds.has(order.id)) {
                                                hasNew = true;
                                                showLiveToast(order);
                                            }
                                        }
                                    });

                                    isFirstLoad = false;
                                    renderNotifications(data.orders);
                                }
                            })
                            .catch(err => console.error("Error checking new orders:", err));
                    };

                    const showLiveToast = (order) => {
                        const toast = document.createElement('div');
                        toast.className = 'fixed bottom-5 right-5 z-[10000] max-w-sm rounded-2xl border border-red-200 bg-white p-4 shadow-2xl transition-all duration-300 transform translate-y-10 opacity-0 flex gap-3 items-start cursor-pointer';
                        toast.innerHTML = `
                            <div class="bg-red-50 p-2 rounded-xl text-red-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-zinc-900">New Order Placed!</p>
                                <p class="text-[11px] text-zinc-500 truncate">Order ${order.order_number} for ₹${parseFloat(order.total_amount).toFixed(0)}</p>
                            </div>
                        `;
                        
                        toast.addEventListener('click', () => {
                            readOrderIds.add(order.id);
                            saveReadOrders();
                            window.location.href = `/admin/orders/${order.order_number}`;
                        });

                        document.body.appendChild(toast);
                        
                        setTimeout(() => {
                            toast.classList.remove('translate-y-10', 'opacity-0');
                        }, 100);

                        setTimeout(() => {
                            toast.classList.add('translate-y-10', 'opacity-0');
                            setTimeout(() => toast.remove(), 300);
                        }, 6000);
                    };

                    bellBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        dropdown.classList.toggle('hidden');
                    });

                    document.addEventListener('click', (e) => {
                        if (!e.target.closest('#admin-notification-container')) {
                            dropdown.classList.add('hidden');
                        }
                    });

                    markReadBtn.addEventListener('click', () => {
                        knownOrderIds.forEach(id => readOrderIds.add(id));
                        saveReadOrders();
                        checkNewOrders();
                    });

                    checkNewOrders();
                    setInterval(checkNewOrders, 5000);
                });
            </script>
        @else
            {{ $slot }}
        @endif
    </body>
</html>
