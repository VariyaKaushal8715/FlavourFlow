@props([
    'site',
    'pageTitle' => null,
    'pageDescription' => null,
    'preserveOnRefresh' => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="{{ $pageDescription ?? $site['meta']['description'] }}">

        <title>{{ $pageTitle ?? $site['meta']['title'] }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="bg-white text-zinc-950 antialiased"
        data-brand-logo="{{ asset($site['brand']['logo']) }}"
        data-auto-theme="{{ ($site['theme']['auto_from_logo'] ?? false) ? 'true' : 'false' }}"
        data-public-page="true"
        data-refresh-policy="{{ $preserveOnRefresh ? 'preserve' : 'home' }}"
        data-home-url="{{ route('home') }}"
        data-wishlist-endpoint="{{ auth()->check() ? route('wishlist.products') : '' }}"
        data-wishlist-store-url="{{ auth()->check() ? route('wishlist.store', ['product' => '__product__']) : '' }}"
        data-wishlist-destroy-url="{{ auth()->check() ? route('wishlist.destroy', ['product' => '__product__']) : '' }}"
        data-cart-summary-url="{{ route('cart.summary') }}"
        data-cart-store-url="{{ route('cart.store', ['product' => '__product__']) }}"
        data-cart-update-url="{{ route('cart.update', ['product' => '__product__']) }}"
        data-cart-destroy-url="{{ route('cart.destroy', ['product' => '__product__']) }}"
        data-login-url="{{ route('login') }}"
        style="
            --brand-primary: {{ $site['theme']['primary'] ?? '#b42318' }};
            --brand-accent: {{ $site['theme']['accent'] ?? '#f4b942' }};
            --brand-ink: {{ $site['theme']['ink'] ?? '#09090b' }};
            --brand-surface: {{ $site['theme']['surface'] ?? '#fff9ed' }};
        "
    >
        <main>
            {{ $slot }}
        </main>

        <footer class="border-t border-zinc-200 bg-zinc-50">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 px-6 py-8 sm:flex-row sm:items-center sm:justify-between lg:px-8">
                <div class="flex items-center gap-3">
                    <img class="h-10 w-10 rounded-lg object-cover" src="{{ asset($site['brand']['logo']) }}" alt="{{ $site['brand']['name'] }} mark">
                    <div>
                        <p class="text-sm font-semibold text-zinc-950">{{ $site['brand']['name'] }}</p>
                        <p class="text-sm text-zinc-500">{{ $site['brand']['tagline'] }}</p>
                    </div>
                </div>

                <nav class="flex flex-wrap gap-x-5 gap-y-3 text-sm font-medium text-zinc-600" aria-label="Footer">
                    @foreach ($site['footer_links'] as $link)
                        <a class="transition hover:text-zinc-950" href="{{ $link['href'] }}">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            </div>
        </footer>
    </body>
</html>
