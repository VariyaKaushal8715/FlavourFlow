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

        <footer class="border-t border-zinc-200 bg-zinc-50">
            <div class="mx-auto grid w-full max-w-7xl gap-8 px-6 py-8 lg:grid-cols-[1fr_1.2fr] lg:px-8">
                <div class="flex flex-col gap-8">
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

                <div class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-brand-primary">{{ $site['footer_location']['label'] }}</p>
                            <h2 class="mt-2 text-lg font-semibold text-zinc-950">{{ $site['footer_location']['name'] }}</h2>
                            <div class="mt-3 space-y-1 text-sm leading-6 text-zinc-600">
                                @foreach ($site['footer_location']['address_lines'] as $line)
                                    <p>{{ $line }}</p>
                                @endforeach
                            </div>
                        </div>

                        <a class="inline-flex min-h-11 shrink-0 items-center rounded-lg border border-zinc-300 bg-zinc-50 px-4 text-sm font-semibold text-zinc-800 transition hover:border-zinc-400 hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-950 focus:ring-offset-2" href="{{ $site['footer_location']['directions_url'] }}" target="_blank" rel="noreferrer">
                            Open maps
                        </a>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-zinc-100">
                        <iframe
                            title="{{ $site['footer_location']['name'] }} location map"
                            class="h-64 w-full"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            src="https://www.google.com/maps?q={{ urlencode($site['footer_location']['maps_query']) }}&output=embed"
                        ></iframe>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>
