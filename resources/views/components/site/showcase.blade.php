@props(['showcase'])

<section id="showcase" class="bg-brand-ink py-20 text-white sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
            <div>
                <p class="text-sm font-semibold text-brand-accent">{{ $showcase['eyebrow'] }}</p>
                <h2 class="mt-3 text-3xl font-semibold leading-tight sm:text-4xl">{{ $showcase['title'] }}</h2>
                <p class="mt-4 text-base leading-8 text-white/70 sm:text-lg">{{ $showcase['description'] }}</p>
            </div>

            <div class="grid gap-4">
                @foreach ($showcase['items'] as $item)
                    <article class="rounded-lg border border-white/10 bg-white/10 p-6">
                        <p class="text-sm font-semibold text-brand-accent">{{ $item['label'] }}</p>
                        <h3 class="mt-3 text-xl font-semibold leading-7">{{ $item['title'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-white/70">{{ $item['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
