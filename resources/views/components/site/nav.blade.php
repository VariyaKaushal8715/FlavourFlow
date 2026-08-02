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
            <a class="transition hover:text-white" href="{{ route('wishlist.index') }}">Wishlist</a>
            <a class="inline-flex items-center gap-2 transition hover:text-white" href="{{ route('cart.index') }}">
                Cart
                <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-white px-1.5 py-0.5 text-xs font-semibold text-zinc-950" data-cart-count>{{ app(\App\Support\CartState::class)->count() }}</span>
            </a>
            @auth
                <span class="text-white/60">Hi, {{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="inline-flex items-center rounded-full border border-white/20 px-4 py-2 text-white transition hover:border-white hover:bg-white/10" type="submit">
                        Sign out
                    </button>
                </form>
            @else
                <a class="inline-flex items-center rounded-full border border-white/20 px-4 py-2 text-white transition hover:border-white hover:bg-white/10" href="{{ route('login') }}">
                    Sign in
                </a>
                <a class="inline-flex items-center rounded-full bg-brand-accent px-4 py-2 font-semibold text-brand-ink transition hover:opacity-90" href="{{ route('register') }}">
                    Create account
                </a>
            @endauth
        </nav>
    </div>
</header>
