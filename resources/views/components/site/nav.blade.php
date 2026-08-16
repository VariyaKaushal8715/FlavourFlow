@props(['brand', 'navigation'])

<header class="relative z-20">
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-5 lg:px-8">
        <a class="flex min-w-0 items-center gap-3 text-white" href="{{ route('home') }}" aria-label="{{ $brand['name'] }} home">
            <img class="h-11 w-11 rounded-lg object-cover ring-1 ring-white/30" src="{{ asset($brand['logo']) }}" alt="{{ $brand['name'] }} mark">
            <span class="min-w-0">
                <span class="block truncate text-sm font-semibold">{{ $brand['name'] }}</span>
                <span class="block truncate text-xs text-white/70">{{ $brand['tagline'] }}</span>
            </span>
        </a>

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
            @auth
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
</header>
