@props(['site'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ $site['meta']['description'] }}">

        <title>{{ $site['meta']['title'] }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white text-zinc-950 antialiased">
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
