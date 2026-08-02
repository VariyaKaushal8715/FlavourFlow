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
        <meta name="description" content="{{ $pageDescription ?? $site['meta']['description'] }}">

        <title>{{ $pageTitle ?? $site['meta']['title'] }}</title>

        @fonts
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

        <x-site.footer :site="$site" />
    </body>
</html>
