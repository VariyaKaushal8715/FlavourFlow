@props([
    'site',
    'pageTitle' => null,
    'pageDescription' => null,
    'preserveOnRefresh' => false,
    'showFooter' => true,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="{{ $pageDescription ?? $site['meta']['description'] }}">

        <title>{{ $pageTitle ?? $site['meta']['title'] }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/flavourflow-mark.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/flavourflow-mark.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        id="top"
        class="bg-white text-zinc-950 antialiased"
        data-brand-logo="{{ asset($site['brand']['logo']) }}"
        data-auto-theme="{{ ($site['theme']['auto_from_logo'] ?? false) ? 'true' : 'false' }}"
        data-public-page="true"
        data-refresh-policy="{{ $preserveOnRefresh ? 'preserve' : 'home' }}"
        data-home-url="{{ route('home') }}"
        data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
        data-wishlist-endpoint="{{ route('wishlist.products') }}"
        data-wishlist-store-url="{{ route('wishlist.store', ['product' => '__product__']) }}"
        data-wishlist-destroy-url="{{ route('wishlist.destroy', ['product' => '__product__']) }}"
        data-cart-summary-url="{{ route('cart.summary') }}"
        data-cart-store-url="{{ route('cart.store', ['product' => '__product__']) }}"
        data-cart-update-url="{{ route('cart.update', ['product' => '__product__']) }}"
        data-cart-destroy-url="{{ route('cart.destroy', ['product' => '__product__']) }}"
        data-cart-clear-url="{{ route('cart.clear') }}"
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

        @if ($showFooter)
            <x-site.footer :site="$site" />
        @endif

        <x-site.ai-assistant />
    </body>
</html>
