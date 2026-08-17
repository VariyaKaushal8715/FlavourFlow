@props(['brand', 'navigation'])

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
                    Sign in
                </a>
            @endguest
            <button
                class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-white/15 bg-white/5 text-white transition hover:bg-white/10"
                type="button"
                data-site-nav-toggle
                aria-label="Open menu"
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
                <a class="transition hover:text-white" href="{{ $item['href'] }}">{{ $item['label'] }}</a>
            @endforeach
            <a class="transition hover:text-white" href="{{ route('wishlist.index') }}">{{ __('ui.wishlist') }}</a>
            <a class="flex items-center gap-2 transition hover:text-white" href="{{ route('cart.index') }}">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M6 5v1H4.667a1.75 1.75 0 00-1.743 1.598l-.826 9.087A1.75 1.75 0 003.84 19h12.32a1.75 1.75 0 001.743-1.815l-.826-9.087A1.75 1.75 0 0015.333 6H14V5a4 4 0 00-8 0zm4-2.5A2.5 2.5 0 007.5 5v1h5V5A2.5 2.5 0 0010 2.5zM7.5 11a.75.75 0 01.75-.75h4a.75.75 0 010 1.5h-4a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium" data-cart-count>{{ app(\App\Support\CartState::class)->count() }}</span>
            </a>
            <div class="flex items-center gap-2 rounded-full border border-white/15 bg-white/5 p-1 text-xs font-semibold text-white/80">
                <a class="@class(['rounded-full px-3 py-1 transition', 'bg-white text-zinc-950' => app()->getLocale() === 'en'])" href="{{ route('language.switch', 'en') }}">{{ __('ui.english') }}</a>
                <a class="@class(['rounded-full px-3 py-1 transition', 'bg-white text-zinc-950' => app()->getLocale() === 'gu'])" href="{{ route('language.switch', 'gu') }}">{{ __('ui.gujarati') }}</a>
            </div>
        </nav>
    </div>

    <div
        class="mx-auto hidden w-full max-w-7xl px-6 pb-5 md:hidden"
        id="site-nav-panel"
        data-site-nav-panel
    >
        <div class="rounded-2xl border border-white/10 bg-zinc-950/95 p-4 shadow-2xl backdrop-blur">
            <div class="grid gap-2 text-sm font-medium text-white/80">
                @foreach ($navigation as $item)
                    <a class="rounded-xl px-4 py-3 transition hover:bg-white/5 hover:text-white" href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                @endforeach
                <a class="rounded-xl px-4 py-3 transition hover:bg-white/5 hover:text-white" href="{{ route('wishlist.index') }}">{{ __('ui.wishlist') }}</a>
                @auth
                    <details class="group rounded-xl transition hover:bg-white/5 hover:text-white">
                        <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 [&::-webkit-details-marker]:hidden">
                            Profile
                            <span class="text-xs transition group-open:rotate-180" aria-hidden="true">v</span>
                        </summary>
                        <div class="px-2 pb-2">
                            <a class="block rounded-xl px-4 py-3 transition hover:bg-white/5 hover:text-white" href="{{ route('account.profile') }}">Profile</a>
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
                    <a class="rounded-xl px-4 py-3 transition hover:bg-white/5 hover:text-white" href="{{ route('login') }}">Sign in</a>
                @endguest
            </div>
            <div class="mt-4 flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 p-1 text-xs font-semibold text-white/80">
                <a class="@class(['rounded-full px-3 py-1 transition', 'bg-white text-zinc-950' => app()->getLocale() === 'en'])" href="{{ route('language.switch', 'en') }}">{{ __('ui.english') }}</a>
                <a class="@class(['rounded-full px-3 py-1 transition', 'bg-white text-zinc-950' => app()->getLocale() === 'gu'])" href="{{ route('language.switch', 'gu') }}">{{ __('ui.gujarati') }}</a>
            </div>
        </div>
    </div>
</header>
