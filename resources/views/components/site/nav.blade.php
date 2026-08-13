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
            @auth
                <details class="group relative">
                    <summary class="flex cursor-pointer list-none items-center gap-1 transition hover:text-white [&::-webkit-details-marker]:hidden">
                        Profile
                        <span class="text-xs transition group-open:rotate-180" aria-hidden="true">v</span>
                    </summary>
                    <div class="absolute right-0 top-full mt-3 min-w-40 rounded-2xl border border-white/10 bg-zinc-950/95 p-2 text-sm font-medium text-white/80 shadow-2xl backdrop-blur">
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
                <a class="inline-flex items-center rounded-full border border-white/20 px-4 py-2 text-white transition hover:border-white hover:bg-white/10" href="{{ route('login') }}">
                    Sign in
                </a>
            @endguest
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
