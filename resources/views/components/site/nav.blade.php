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

        <button
            class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-white/15 bg-white/5 text-white transition hover:bg-white/10 md:hidden"
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

        <nav class="hidden items-center gap-7 text-sm font-medium text-white/75 md:flex" aria-label="Primary">
            @foreach ($navigation as $item)
                <a class="transition hover:text-white" href="{{ $item['href'] }}">{{ $item['label'] }}</a>
            @endforeach
            <a class="transition hover:text-white" href="{{ route('wishlist.index') }}">{{ __('ui.wishlist') }}</a>
            <div class="flex items-center gap-2 rounded-full border border-white/15 bg-white/5 p-1 text-xs font-semibold text-white/80">
                <a class="@class(['rounded-full px-3 py-1 transition', 'bg-white text-zinc-950' => app()->getLocale() === 'en'])" href="{{ route('language.switch', 'en') }}">{{ __('ui.english') }}</a>
                <a class="@class(['rounded-full px-3 py-1 transition', 'bg-white text-zinc-950' => app()->getLocale() === 'gu'])" href="{{ route('language.switch', 'gu') }}">{{ __('ui.gujarati') }}</a>
            </div>
            @auth
                <a class="transition hover:text-white" href="{{ route('account.profile') }}">{{ __('ui.account') }}</a>
                <span class="text-white/60">Hi, {{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="inline-flex items-center rounded-full border border-white/20 px-4 py-2 text-white transition hover:border-white hover:bg-white/10" type="submit">
                        {{ __('ui.sign_out') }}
                    </button>
                </form>
            @endauth
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
                @auth
                    <a class="rounded-xl px-4 py-3 transition hover:bg-white/5 hover:text-white" href="{{ route('account.profile') }}">{{ __('ui.account') }}</a>
                @endauth
                <a class="rounded-xl px-4 py-3 transition hover:bg-white/5 hover:text-white" href="{{ route('wishlist.index') }}">{{ __('ui.wishlist') }}</a>
            </div>
            <div class="mt-4 flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 p-1 text-xs font-semibold text-white/80">
                <a class="@class(['rounded-full px-3 py-1 transition', 'bg-white text-zinc-950' => app()->getLocale() === 'en'])" href="{{ route('language.switch', 'en') }}">{{ __('ui.english') }}</a>
                <a class="@class(['rounded-full px-3 py-1 transition', 'bg-white text-zinc-950' => app()->getLocale() === 'gu'])" href="{{ route('language.switch', 'gu') }}">{{ __('ui.gujarati') }}</a>
            </div>
            @auth
                <div class="mt-4 flex items-center justify-between gap-3">
                    <a class="text-sm text-white/60 transition hover:text-white" href="{{ route('account.profile') }}">{{ __('ui.account') }}: {{ auth()->user()->name }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="inline-flex items-center rounded-full border border-white/20 px-4 py-2 text-white transition hover:border-white hover:bg-white/10" type="submit">
                            {{ __('ui.sign_out') }}
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</header>
