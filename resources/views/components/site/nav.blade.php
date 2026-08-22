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

<header class="relative z-20">
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-6 py-5 lg:px-8">
        <a class="flex min-w-0 items-center gap-3 text-white" href="{{ route('home') }}" aria-label="{{ $brand['name'] }} home">
            <img class="h-11 w-11 rounded-lg object-cover ring-1 ring-white/30" src="{{ asset($brand['logo']) }}" alt="{{ $brand['name'] }} mark">
            <span class="min-w-0">
                <span class="block truncate text-sm font-semibold">{{ $brand['name'] }}</span>
                <span class="block truncate text-xs text-white/70">{{ $brand['tagline'] }}</span>
            </span>
        </a>

        <div class="flex items-center gap-2 md:hidden">
            @guest
                <a class="inline-flex h-11 items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/15" href="{{ route('login') }}">
                    {{ __('ui.sign_in') }}
                </a>
            @endguest
            <button
                class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-white/15 bg-white/5 text-white transition hover:bg-white/10"
                type="button"
                data-site-nav-toggle
                aria-label="{{ __('ui.open_menu') }}"
                aria-expanded="false"
                aria-controls="site-nav-panel"
            >
                <span class="flex flex-col gap-1.5">
                    <span class="h-0.5 w-5 rounded-full bg-current"></span>
                    <span class="h-0.5 w-5 rounded-full bg-current"></span>
                    <span class="h-0.5 w-5 rounded-full bg-current"></span>
                </span>
            </button>
        </div>

        <nav class="hidden items-center gap-7 text-sm font-medium text-white/75 md:flex" aria-label="Primary">
            @foreach ($navigation as $item)
                <a class="transition hover:text-white" href="{{ $item['href'] }}">{{ $navLabels[$item['label']] ?? $item['label'] }}</a>
            @endforeach
            <a class="transition hover:text-white" href="{{ route('wishlist.index') }}">{{ __('ui.wishlist') }}</a>
            <a class="flex items-center gap-2 transition hover:text-white" href="{{ route('cart.index') }}">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M6 5v1H4.667a1.75 1.75 0 00-1.743 1.598l-.826 9.087A1.75 1.75 0 003.84 19h12.32a1.75 1.75 0 001.743-1.815l-.826-9.087A1.75 1.75 0 0015.333 6H14V5a4 4 0 00-8 0zm4-2.5A2.5 2.5 0 007.5 5v1h5V5A2.5 2.5 0 0010 2.5zM7.5 11a.75.75 0 01.75-.75h4a.75.75 0 010 1.5h-4a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium" data-cart-count>{{ app(\App\Support\CartState::class)->count() }}</span>
            </a>

            {{-- Language Switcher (Desktop) --}}
            <details class="group relative" id="lang-switcher-desktop">
                <summary class="flex cursor-pointer list-none items-center gap-2 text-white/75 transition hover:text-white [&::-webkit-details-marker]:hidden">
                    <span>{{ __('ui.languages') }}</span>
                    <svg class="h-3 w-3 shrink-0 transition duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                </summary>

                <div class="absolute right-0 top-full z-50 mt-2 w-44 overflow-hidden rounded-2xl border border-white/10 bg-zinc-950/95 shadow-[0_16px_48px_rgba(0,0,0,0.6)] backdrop-blur-xl">
                    {{-- Header label --}}
                    <div class="border-b border-white/[0.07] px-4 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-white/40">{{ __('ui.languages') }}</p>
                    </div>
                    <div class="p-1.5 space-y-0.5">
                        {{-- English --}}
                        <a
                            href="{{ route('language.switch', 'en') }}"
                            @class([
                                'group/item flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                                'bg-amber-400/10 text-amber-400' => $currentLocale === 'en',
                                'text-white/70 hover:bg-white/[0.06] hover:text-white' => $currentLocale !== 'en',
                            ])
                        >
                            <span>English</span>
                            @if ($currentLocale === 'en')
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </a>
                        {{-- Gujarati --}}
                        <a
                            href="{{ route('language.switch', 'gu') }}"
                            @class([
                                'group/item flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                                'bg-amber-400/10 text-amber-400' => $currentLocale === 'gu',
                                'text-white/70 hover:bg-white/[0.06] hover:text-white' => $currentLocale !== 'gu',
                            ])
                        >
                            <span>ગુજરાતી</span>
                            @if ($currentLocale === 'gu')
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </a>
                        {{-- Hindi --}}
                        <a
                            href="{{ route('language.switch', 'hi') }}"
                            @class([
                                'group/item flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                                'bg-amber-400/10 text-amber-400' => $currentLocale === 'hi',
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
                <details class="group relative">
                    <summary class="flex cursor-pointer list-none items-center gap-2 text-white/75 transition hover:text-white [&::-webkit-details-marker]:hidden">
                        <span>{{ __('ui.profile') }}</span>
                        <svg class="h-3 w-3 shrink-0 transition duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="absolute right-0 top-full z-50 mt-2 w-40 overflow-hidden rounded-2xl border border-white/10 bg-zinc-950/95 p-1.5 shadow-[0_16px_48px_rgba(0,0,0,0.6)] backdrop-blur-xl">
                        <a class="flex rounded-xl px-3 py-2.5 text-sm font-medium text-white/70 transition hover:bg-white/[0.06] hover:text-white" href="{{ route('account.profile') }}">{{ __('ui.profile') }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full rounded-xl px-3 py-2.5 text-left text-sm font-medium text-white/70 transition hover:bg-white/[0.06] hover:text-white" type="submit">
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

    {{-- Mobile Panel --}}
    <div
        class="mx-auto hidden w-full max-w-7xl px-6 pb-5 md:hidden"
        id="site-nav-panel"
        data-site-nav-panel
    >
        <div class="rounded-2xl border border-white/10 bg-zinc-950/95 p-4 shadow-2xl backdrop-blur">
            <div class="grid gap-0.5 text-sm font-medium text-white/80">
                @foreach ($navigation as $item)
                    <a class="rounded-xl px-4 py-3 transition hover:bg-white/5 hover:text-white" href="{{ $item['href'] }}">{{ $navLabels[$item['label']] ?? $item['label'] }}</a>
                @endforeach
                <a class="rounded-xl px-4 py-3 transition hover:bg-white/5 hover:text-white" href="{{ route('wishlist.index') }}">{{ __('ui.wishlist') }}</a>
                @auth
                    <details class="group rounded-xl transition hover:bg-white/5 hover:text-white">
                        <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 [&::-webkit-details-marker]:hidden">
                            {{ __('ui.profile') }}
                            <svg class="h-3.5 w-3.5 transition duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                        </summary>
                        <div class="px-2 pb-2">
                            <a class="block rounded-xl px-4 py-3 transition hover:bg-white/5 hover:text-white" href="{{ route('account.profile') }}">{{ __('ui.profile') }}</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="w-full rounded-xl px-4 py-3 text-left transition hover:bg-white/5 hover:text-white" type="submit">
                                    {{ __('ui.sign_out') }}
                                </button>
                            </form>
                        </div>
                    </details>
                @endauth
                @guest
                    <a class="rounded-xl px-4 py-3 transition hover:bg-white/5 hover:text-white" href="{{ route('login') }}">{{ __('ui.sign_in') }}</a>
                @endguest
            </div>

            {{-- Language Switcher (Mobile) --}}
            <div class="mt-3 overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03]">
                {{-- Header --}}
                <div class="border-b border-white/[0.07] px-4 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-white/40">{{ __('ui.languages') }}</p>
                </div>
                <div class="p-2 grid grid-cols-3 gap-1.5">
                    {{-- English --}}
                    <a
                        href="{{ route('language.switch', 'en') }}"
                        @class([
                            'flex flex-col items-center gap-1 rounded-xl px-2 py-3 text-center text-xs font-semibold transition',
                            'bg-amber-400/10 text-amber-400 ring-1 ring-amber-400/20' => $currentLocale === 'en',
                            'text-white/60 hover:bg-white/[0.06] hover:text-white' => $currentLocale !== 'en',
                        ])
                    >
                        <span class="text-base leading-none">🇬🇧</span>
                        <span>EN</span>
                        @if ($currentLocale === 'en')
                            <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </a>
                    {{-- Gujarati --}}
                    <a
                        href="{{ route('language.switch', 'gu') }}"
                        @class([
                            'flex flex-col items-center gap-1 rounded-xl px-2 py-3 text-center text-xs font-semibold transition',
                            'bg-amber-400/10 text-amber-400 ring-1 ring-amber-400/20' => $currentLocale === 'gu',
                            'text-white/60 hover:bg-white/[0.06] hover:text-white' => $currentLocale !== 'gu',
                        ])
                    >
                        <span class="text-base leading-none">🇮🇳</span>
                        <span>ગુ</span>
                        @if ($currentLocale === 'gu')
                            <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </a>
                    {{-- Hindi --}}
                    <a
                        href="{{ route('language.switch', 'hi') }}"
                        @class([
                            'flex flex-col items-center gap-1 rounded-xl px-2 py-3 text-center text-xs font-semibold transition',
                            'bg-amber-400/10 text-amber-400 ring-1 ring-amber-400/20' => $currentLocale === 'hi',
                            'text-white/60 hover:bg-white/[0.06] hover:text-white' => $currentLocale !== 'hi',
                        ])
                    >
                        <span class="text-base leading-none">🇮🇳</span>
                        <span>हि</span>
                        @if ($currentLocale === 'hi')
                            <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
