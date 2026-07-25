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
        </nav>
    </div>
</header>
