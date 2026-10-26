@props(['brand', 'navigation'])

@php
    $navLabels = [
        'Home' => __('ui.home'),
        'Offers' => __('ui.offers'),
        'Products' => __('ui.products'),
        'Our story' => __('ui.our_story'),
    ];

    $currentLocale = app()->getLocale();
    $localeLabel = match($currentLocale) {
        'gu' => 'ગુ',
        'hi' => 'हि',
        default => 'EN',
    };
@endphp

<style>
    summary::-webkit-details-marker,
    summary::marker {
        display: none !important;
    }
</style>

<header class="relative z-20">
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-3 px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
        {{-- Brand Logo --}}
        <a class="flex min-w-0 items-center gap-2.5 sm:gap-3 text-white group" href="{{ route('home') }}" aria-label="{{ $brand['name'] }} home">
            <img class="h-9 w-9 sm:h-11 sm:w-11 rounded-xl object-cover ring-1 ring-amber-400/30 shadow-md shrink-0 transition group-hover:scale-105" src="{{ asset($brand['logo']) }}" alt="{{ $brand['name'] }} mark">
            <span class="min-w-0">
                <span class="block truncate text-xs sm:text-sm font-bold tracking-tight text-white group-hover:text-amber-200 transition">{{ $brand['name'] }}</span>
                <span class="block truncate text-[0.65rem] sm:text-xs text-amber-200/70">{{ $brand['tagline'] }}</span>
            </span>
        </a>

        {{-- Mobile Controls (Cart + Sign in + Menu Button) --}}
        <div class="flex items-center gap-2 md:hidden">
            {{-- Quick Cart Button on Mobile Header --}}
            <a
                class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/15 bg-white/5 text-white transition hover:bg-white/10 active:scale-95"
                href="{{ route('cart.index') }}"
                aria-label="{{ __('ui.cart') }}"
            >
                <svg class="h-5 w-5 text-amber-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M6 5v1H4.667a1.75 1.75 0 00-1.743 1.598l-.826 9.087A1.75 1.75 0 003.84 19h12.32a1.75 1.75 0 001.743-1.815l-.826-9.087A1.75 1.75 0 0015.333 6H14V5a4 4 0 00-8 0zm4-2.5A2.5 2.5 0 007.5 5v1h5V5A2.5 2.5 0 0010 2.5zM7.5 11a.75.75 0 01.75-.75h4a.75.75 0 010 1.5h-4a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
                </svg>
                <span class="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-gradient-to-r from-red-600 to-amber-500 px-1 text-[10px] font-bold text-white ring-2 ring-zinc-950" data-cart-count>{{ app(\App\Support\CartState::class)->count() }}</span>
            </a>

            @guest
                <a class="inline-flex h-10 items-center justify-center rounded-xl border border-white/20 bg-white/10 px-3 text-xs font-semibold text-white transition hover:bg-white/15 active:scale-95" href="{{ route('login') }}">
                    {{ __('ui.sign_in') }}
                </a>
            @endguest

            {{-- Hamburger Toggle Button with Morphing Animation --}}
            <button
                class="group relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/30 bg-zinc-900/80 text-white transition hover:border-amber-400 hover:bg-zinc-800/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400/50 active:scale-95"
                type="button"
                data-site-nav-toggle
                aria-label="{{ __('ui.open_menu') }}"
                aria-expanded="false"
                aria-controls="site-nav-panel"
            >
                <span class="flex h-5 w-5 flex-col items-center justify-center gap-1.5" aria-hidden="true">
                    <span class="h-0.5 w-5 rounded-full bg-amber-200 transition-all duration-300 group-aria-expanded:translate-y-2 group-aria-expanded:rotate-45"></span>
                    <span class="h-0.5 w-5 rounded-full bg-amber-200 transition-all duration-300 group-aria-expanded:opacity-0 group-aria-expanded:scale-0"></span>
                    <span class="h-0.5 w-5 rounded-full bg-amber-200 transition-all duration-300 group-aria-expanded:-translate-y-2 group-aria-expanded:-rotate-45"></span>
                </span>
            </button>
        </div>

        {{-- Desktop Navigation --}}
        <nav class="hidden items-center gap-7 text-sm font-medium text-white/75 md:flex" aria-label="Primary">
            @foreach ($navigation as $item)
                <a class="transition hover:text-white" href="{{ $item['href'] }}">{{ $navLabels[$item['label']] ?? $item['label'] }}</a>
            @endforeach
            <a class="transition hover:text-white" href="{{ route('wishlist.index') }}">{{ __('ui.wishlist') }}</a>
            <a class="flex items-center gap-2 transition hover:text-white" href="{{ route('cart.index') }}">
                <svg class="h-5 w-5 text-amber-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M6 5v1H4.667a1.75 1.75 0 00-1.743 1.598l-.826 9.087A1.75 1.75 0 003.84 19h12.32a1.75 1.75 0 001.743-1.815l-.826-9.087A1.75 1.75 0 0015.333 6H14V5a4 4 0 00-8 0zm4-2.5A2.5 2.5 0 007.5 5v1h5V5A2.5 2.5 0 0010 2.5zM7.5 11a.75.75 0 01.75-.75h4a.75.75 0 010 1.5h-4a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-semibold text-amber-300" data-cart-count>{{ app(\App\Support\CartState::class)->count() }}</span>
            </a>

            {{-- Language Switcher (Desktop) --}}
            <details class="group relative" id="lang-switcher-desktop">
                <summary class="flex cursor-pointer list-none items-center gap-2 text-white/75 transition hover:text-white [&::-webkit-details-marker]:hidden">
                    <span>{{ __('ui.languages') }}</span>
                    <svg class="h-3 w-3 shrink-0 transition duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                </summary>

                <div class="absolute right-0 top-full z-50 mt-2 w-44 overflow-hidden rounded-2xl border border-white/10 bg-zinc-950/95 shadow-[0_16px_48px_rgba(0,0,0,0.6)] backdrop-blur-xl">
                    <div class="border-b border-white/[0.07] px-4 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-white/40">{{ __('ui.languages') }}</p>
                    </div>
                    <div class="p-1.5 space-y-0.5">
                        <a
                            href="{{ route('language.switch', 'en') }}"
                            @class([
                                'group/item flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                                'bg-amber-400/10 text-amber-400 font-semibold' => $currentLocale === 'en',
                                'text-white/70 hover:bg-white/[0.06] hover:text-white' => $currentLocale !== 'en',
                            ])
                        >
                            <span>English</span>
                            @if ($currentLocale === 'en')
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </a>
                        <a
                            href="{{ route('language.switch', 'gu') }}"
                            @class([
                                'group/item flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                                'bg-amber-400/10 text-amber-400 font-semibold' => $currentLocale === 'gu',
                                'text-white/70 hover:bg-white/[0.06] hover:text-white' => $currentLocale !== 'gu',
                            ])
                        >
                            <span>ગુજરાતી</span>
                            @if ($currentLocale === 'gu')
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </a>
                        <a
                            href="{{ route('language.switch', 'hi') }}"
                            @class([
                                'group/item flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                                'bg-amber-400/10 text-amber-400 font-semibold' => $currentLocale === 'hi',
                                'text-white/70 hover:bg-white/[0.06] hover:text-white' => $currentLocale !== 'hi',
                            ])
                        >
                            <span>हिन्दी</span>
                            @if ($currentLocale === 'hi')
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </a>
                    </div>
                </div>
            </details>

            @auth
                @can('access-admin')
                    <a class="flex items-center gap-1.5 rounded-lg border border-amber-500/30 bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-300 transition hover:bg-amber-500/20 hover:text-amber-200" href="{{ route('admin.index') }}">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <span>Admin</span>
                    </a>
                @endcan

                <a class="flex items-center gap-1.5 transition hover:text-white" href="{{ route('account.coupons') }}">
                    <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                    </svg>
                    <span>My Coupons</span>
                </a>

                <a class="flex items-center gap-1.5 transition hover:text-white" href="{{ route('account.orders') }}">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 003 3.5v13A1.5 1.5 0 004.5 18h11a1.5 1.5 0 001.5-1.5V7.621a1.5 1.5 0 00-.44-1.06l-4.12-4.122A1.5 1.5 0 0011.378 2H4.5zm2.25 8.5a.75.75 0 000 1.5h6.5a.75.75 0 000-1.5h-6.5zm0 3a.75.75 0 000 1.5h6.5a.75.75 0 000-1.5h-6.5z" clip-rule="evenodd" />
                    </svg>
                    <span>My Orders</span>
                </a>

                <details class="group relative">
                    <summary class="flex cursor-pointer list-none items-center gap-2 text-white/75 transition hover:text-white [&::-webkit-details-marker]:hidden">
                        <span>{{ __('ui.profile') }}</span>
                        <svg class="h-3 w-3 shrink-0 transition duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="absolute right-0 top-full z-50 mt-2 w-48 overflow-hidden rounded-2xl border border-white/10 bg-zinc-950/95 p-1.5 shadow-[0_16px_48px_rgba(0,0,0,0.6)] backdrop-blur-xl">
                        @can('access-admin')
                            <a class="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-amber-300 transition hover:bg-white/[0.06]" href="{{ route('admin.index') }}">
                                <span>Admin Dashboard</span>
                                <span class="rounded bg-amber-400/20 px-1.5 py-0.5 text-[10px] font-bold uppercase text-amber-300">Admin</span>
                            </a>
                            <div class="my-1 border-t border-white/[0.07]"></div>
                        @endcan
                        <a class="flex rounded-xl px-3 py-2.5 text-sm font-medium text-white/70 transition hover:bg-white/[0.06] hover:text-white" href="{{ route('account.profile') }}">{{ __('ui.profile') }}</a>
                        <a class="flex rounded-xl px-3 py-2.5 text-sm font-medium text-white/70 transition hover:bg-white/[0.06] hover:text-white" href="{{ route('account.orders') }}">My Orders</a>
                        <a class="flex rounded-xl px-3 py-2.5 text-sm font-medium text-white/70 transition hover:bg-white/[0.06] hover:text-white" href="{{ route('account.coupons') }}">My Coupons</a>
                        <div class="my-1 border-t border-white/[0.07]"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full rounded-xl px-3 py-2.5 text-left text-sm font-medium text-red-300/80 transition hover:bg-red-500/10 hover:text-red-300" type="submit">
                                {{ __('ui.sign_out') }}
                            </button>
                        </form>
                    </div>
                </details>
            @endauth
            @guest
                <a class="inline-flex h-9 items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/15" href="{{ route('login') }}">
                    {{ __('ui.sign_in') }}
                </a>
            @endguest
        </nav>
    </div>

    {{-- Redesigned Mobile Navigation Panel --}}
    <div
        class="mx-auto hidden w-full max-w-7xl px-3 sm:px-6 pb-6 md:hidden"
        id="site-nav-panel"
        data-site-nav-panel
    >
        <div class="overflow-hidden rounded-3xl border border-amber-500/25 bg-gradient-to-b from-zinc-950/98 via-zinc-900/95 to-zinc-950/98 p-4 sm:p-5 shadow-[0_24px_70px_rgba(0,0,0,0.75),0_0_40px_rgba(245,158,11,0.08)] backdrop-blur-2xl ring-1 ring-white/10 space-y-4">
            {{-- User / Auth Header --}}
            @auth
                @php
                    $navUser = auth()->user();
                    $navInitials = collect(explode(' ', $navUser->name))
                        ->filter()
                        ->take(2)
                        ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                        ->join('');
                @endphp
                <div class="rounded-2xl border border-amber-500/20 bg-white/[0.04] p-3.5 backdrop-blur-md">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-red-600 via-amber-600 to-amber-500 text-sm font-bold text-white shadow-md ring-2 ring-amber-400/30">
                            {{ $navInitials ?: 'U' }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-white">{{ $navUser->name }}</p>
                            <p class="truncate text-xs text-amber-200/70">{{ $navUser->email }}</p>
                        </div>
                        <a href="{{ route('account.profile') }}" class="shrink-0 rounded-xl border border-amber-400/30 bg-amber-400/10 px-3 py-1.5 text-xs font-semibold text-amber-300 transition hover:bg-amber-400/20">
                            {{ __('ui.profile') }}
                        </a>
                    </div>

                    {{-- Admin Access in Mobile Menu --}}
                    @can('access-admin')
                        <a href="{{ route('admin.index') }}" class="mt-3 flex items-center justify-between rounded-xl border border-amber-500/40 bg-gradient-to-r from-amber-500/20 via-red-600/20 to-amber-500/20 px-3.5 py-2.5 text-xs font-semibold text-amber-300 transition hover:border-amber-400 hover:bg-amber-500/30 active:scale-[0.99]">
                            <span class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                                <span>Admin Dashboard</span>
                            </span>
                            <span class="rounded-full bg-amber-400/25 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-300">Admin Access</span>
                        </a>
                    @endcan
                </div>
            @endauth

            @guest
                <div class="rounded-2xl border border-amber-500/20 bg-white/[0.03] p-3.5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-300/90">Welcome to FlavourFlow</p>
                    <p class="mt-0.5 text-xs text-white/60">Small-batch authentic Indian spices</p>
                    <div class="mt-3 flex gap-2">
                        <a class="flex-1 inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-red-700 to-amber-500 px-3 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-md transition hover:brightness-110 active:scale-95" href="{{ route('login') }}">
                            {{ __('ui.sign_in') }}
                        </a>
                        <a class="flex-1 inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/10 px-3 py-2.5 text-xs sm:text-sm font-semibold text-white transition hover:bg-white/15 active:scale-95" href="{{ route('register') }}">
                            {{ __('ui.register') }}
                        </a>
                    </div>
                </div>
            @endguest

            {{-- Primary Navigation Links --}}
            <nav class="space-y-1" aria-label="Mobile Primary">
                @foreach ($navigation as $item)
                    @php
                        $isActive = request()->fullUrlIs($item['href']) || (request()->url() === url($item['href']));
                    @endphp
                    <a
                        href="{{ $item['href'] }}"
                        @class([
                            'group flex items-center justify-between min-h-[46px] rounded-xl px-3.5 py-2.5 text-sm transition',
                            'bg-gradient-to-r from-amber-500/20 via-amber-500/10 to-transparent text-amber-300 font-semibold border-l-2 border-amber-400' => $isActive,
                            'text-white/80 hover:bg-white/[0.06] hover:text-white font-medium' => !$isActive,
                        ])
                    >
                        <span>{{ $navLabels[$item['label']] ?? $item['label'] }}</span>
                        <svg class="h-4 w-4 text-white/30 transition group-hover:text-amber-300 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-white/[0.08]"></div>

            {{-- Shopping & Account Shortcuts (No repetition!) --}}
            <div class="grid grid-cols-2 gap-2 text-sm">
                {{-- Wishlist --}}
                <a
                    href="{{ route('wishlist.index') }}"
                    @class([
                        'flex items-center gap-2.5 min-h-[44px] rounded-xl px-3.5 py-2.5 font-medium transition border',
                        'border-amber-400/30 bg-amber-400/15 text-amber-300 font-semibold' => request()->routeIs('wishlist.*'),
                        'border-white/10 bg-white/[0.03] text-white/80 hover:bg-white/[0.07] hover:text-white' => !request()->routeIs('wishlist.*'),
                    ])
                >
                    <svg class="h-4 w-4 text-red-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m12 21-1.45-1.32C5.4 15 2 11.92 2 8.15 2 5.07 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.07 22 8.15c0 3.77-3.4 6.85-8.55 11.54L12 21Z" />
                    </svg>
                    <span class="truncate">{{ __('ui.wishlist') }}</span>
                </a>

                {{-- Cart --}}
                <a
                    href="{{ route('cart.index') }}"
                    @class([
                        'flex items-center justify-between min-h-[44px] rounded-xl px-3.5 py-2.5 font-medium transition border',
                        'border-amber-400/30 bg-amber-400/15 text-amber-300 font-semibold' => request()->routeIs('cart.*'),
                        'border-white/10 bg-white/[0.03] text-white/80 hover:bg-white/[0.07] hover:text-white' => !request()->routeIs('cart.*'),
                    ])
                >
                    <span class="flex items-center gap-2.5 truncate">
                        <svg class="h-4 w-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span class="truncate">{{ __('ui.cart') }}</span>
                    </span>
                    <span class="rounded-full bg-amber-500/25 px-2 py-0.5 text-xs font-bold text-amber-300" data-cart-count>{{ app(\App\Support\CartState::class)->count() }}</span>
                </a>

                @auth
                    {{-- My Orders --}}
                    <a
                        href="{{ route('account.orders') }}"
                        @class([
                            'flex items-center gap-2.5 min-h-[44px] rounded-xl px-3.5 py-2.5 font-medium transition border',
                            'border-amber-400/30 bg-amber-400/15 text-amber-300 font-semibold' => request()->routeIs('account.orders*'),
                            'border-white/10 bg-white/[0.03] text-white/80 hover:bg-white/[0.07] hover:text-white' => !request()->routeIs('account.orders*'),
                        ])
                    >
                        <svg class="h-4 w-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                        <span class="truncate">My Orders</span>
                    </a>

                    {{-- My Coupons --}}
                    <a
                        href="{{ route('account.coupons') }}"
                        @class([
                            'flex items-center gap-2.5 min-h-[44px] rounded-xl px-3.5 py-2.5 font-medium transition border',
                            'border-amber-400/30 bg-amber-400/15 text-amber-300 font-semibold' => request()->routeIs('account.coupons*'),
                            'border-white/10 bg-white/[0.03] text-white/80 hover:bg-white/[0.07] hover:text-white' => !request()->routeIs('account.coupons*'),
                        ])
                    >
                        <svg class="h-4 w-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                        </svg>
                        <span class="truncate">My Coupons</span>
                    </a>
                @endauth
            </div>

            {{-- Sign Out Button (Auth) --}}
            @auth
                <div class="pt-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="w-full flex items-center justify-center gap-2 min-h-[44px] rounded-xl border border-red-500/25 bg-red-500/10 py-2.5 px-4 text-xs font-semibold text-red-300 transition hover:bg-red-500/20 active:scale-95" type="submit">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                            <span>{{ __('ui.sign_out') }}</span>
                        </button>
                    </form>
                </div>
            @endauth

            {{-- Language Selector (Mobile) --}}
            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.02]">
                <div class="border-b border-white/[0.06] px-3.5 py-2 flex items-center justify-between">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-white/50">{{ __('ui.languages') }}</p>
                    <span class="text-[10px] text-amber-300/80 font-medium">{{ $localeLabel }} Selected</span>
                </div>
                <div class="p-2 grid grid-cols-3 gap-2">
                    {{-- English --}}
                    <a
                        href="{{ route('language.switch', 'en') }}"
                        @class([
                            'flex flex-col items-center justify-center gap-1 rounded-xl px-2 py-2.5 text-center min-h-[44px] transition',
                            'bg-gradient-to-b from-amber-500/25 to-amber-600/15 text-amber-300 ring-1 ring-amber-400/40 shadow-inner font-semibold' => $currentLocale === 'en',
                            'bg-white/[0.04] text-white/70 hover:bg-white/[0.08] hover:text-white border border-white/5 font-medium' => $currentLocale !== 'en',
                        ])
                    >
                        <span class="text-sm leading-none">🇬🇧</span>
                        <span class="text-xs">English</span>
                    </a>
                    {{-- Gujarati --}}
                    <a
                        href="{{ route('language.switch', 'gu') }}"
                        @class([
                            'flex flex-col items-center justify-center gap-1 rounded-xl px-2 py-2.5 text-center min-h-[44px] transition',
                            'bg-gradient-to-b from-amber-500/25 to-amber-600/15 text-amber-300 ring-1 ring-amber-400/40 shadow-inner font-semibold' => $currentLocale === 'gu',
                            'bg-white/[0.04] text-white/70 hover:bg-white/[0.08] hover:text-white border border-white/5 font-medium' => $currentLocale !== 'gu',
                        ])
                    >
                        <span class="text-sm leading-none">🇮🇳</span>
                        <span class="text-xs">ગુજરાતી</span>
                    </a>
                    {{-- Hindi --}}
                    <a
                        href="{{ route('language.switch', 'hi') }}"
                        @class([
                            'flex flex-col items-center justify-center gap-1 rounded-xl px-2 py-2.5 text-center min-h-[44px] transition',
                            'bg-gradient-to-b from-amber-500/25 to-amber-600/15 text-amber-300 ring-1 ring-amber-400/40 shadow-inner font-semibold' => $currentLocale === 'hi',
                            'bg-white/[0.04] text-white/70 hover:bg-white/[0.08] hover:text-white border border-white/5 font-medium' => $currentLocale !== 'hi',
                        ])
                    >
                        <span class="text-sm leading-none">🇮🇳</span>
                        <span class="text-xs">हिन्दी</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

