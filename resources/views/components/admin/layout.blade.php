@props(['title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ $title }} - FlavourFlow Admin</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-100 text-zinc-950 antialiased" data-admin-secure-page="true">
        @php
            $activePage = request()->routeIs('admin.products*') ? 'products' : (request()->routeIs('admin.offers*') ? 'offers' : 'dashboard');
        @endphp

        <div class="flex min-h-screen flex-row bg-zinc-100">
            <aside class="sticky top-0 flex h-screen w-72 flex-none flex-col overflow-y-auto border-r border-zinc-800 bg-zinc-950 text-white">
                <div class="flex items-center gap-3 border-b border-white/10 px-6 py-5">
                    <img class="h-11 w-11 rounded-lg object-cover ring-1 ring-white/10" src="{{ asset('images/flavourflow-mark.png') }}" alt="FlavourFlow">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold">FlavourFlow Admin</p>
                        <p class="truncate text-xs text-white/60">Catalog, inventory, offers</p>
                    </div>
                </div>

                <nav class="flex-1 px-4 py-5" aria-label="Admin">
                    <div class="space-y-2">
                        <a @class([
                            'flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold transition',
                            'bg-white text-zinc-950 shadow-sm' => $activePage === 'dashboard',
                            'text-white/70 hover:bg-white/5 hover:text-white' => $activePage !== 'dashboard',
                        ]) href="{{ route('admin.index') }}">
                            <span>Products</span>
                            <span class="text-xs uppercase tracking-[0.2em] text-current/60">Manage</span>
                        </a>
                        <a @class([
                            'flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold transition',
                            'bg-white text-zinc-950 shadow-sm' => request()->routeIs('admin.categories*'),
                            'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.categories*'),
                        ]) href="{{ route('admin.categories.index') }}">
                            <span>Categories</span>
                            <span class="text-xs uppercase tracking-[0.2em] text-current/60">Menu</span>
                        </a>
                        <a @class([
                            'flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold transition',
                            'bg-white text-zinc-950 shadow-sm' => request()->routeIs('admin.inventory*'),
                            'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.inventory*'),
                        ]) href="{{ route('admin.inventory.index') }}">
                            <span>Inventory</span>
                            <span class="text-xs uppercase tracking-[0.2em] text-current/60">Menu</span>
                        </a>
                        <a @class([
                            'flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold transition',
                            'bg-white text-zinc-950 shadow-sm' => $activePage === 'offers',
                            'text-white/70 hover:bg-white/5 hover:text-white' => $activePage !== 'offers',
                        ]) href="{{ route('admin.offers.index') }}">
                            <span>Offers</span>
                            <span class="text-xs uppercase tracking-[0.2em] text-current/60">Campaigns</span>
                        </a>
                    </div>
                </nav>

                <div class="border-t border-white/10 p-4">
                    <a class="mb-3 flex items-center justify-center rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10" href="{{ route('home') }}" target="_blank" rel="noreferrer">
                        View live site
                    </a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="flex w-full items-center justify-center rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10" type="submit">
                            Sign out
                        </button>
                    </form>
                </div>
            </aside>

            <div class="min-w-0 flex-1">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
