@props(['company'])

<section id="company" class="bg-brand-ink py-20 text-white sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:gap-20">
            <div data-reveal>
                <p class="text-sm font-semibold text-brand-accent">{{ $company['eyebrow'] }}</p>
                <h2 class="mt-4 text-3xl font-semibold leading-tight sm:text-5xl">{{ $company['title'] }}</h2>
                <p class="mt-6 max-w-xl text-base leading-8 text-white/70">{{ $company['description'] }}</p>
            </div>

            <div class="grid grid-cols-2 border-y border-white/15 sm:grid-cols-4 lg:self-start" data-reveal>
                @foreach ($company['facts'] as $fact)
                    <div class="border-white/15 px-4 py-6 not-first:border-l">
                        <p class="text-2xl font-semibold text-brand-accent">{{ $fact['value'] }}</p>
                        <p class="mt-2 text-xs leading-5 text-white/55">{{ $fact['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-14 grid gap-8 border-t border-white/15 pt-10 md:grid-cols-3">
            @foreach ($company['principles'] as $index => $principle)
                <article class="grid grid-cols-[2rem_1fr] gap-4" data-reveal data-reveal-delay="{{ $index * 90 }}">
                    <span class="text-sm font-semibold text-brand-accent">0{{ $index + 1 }}</span>
                    <div>
                        <h3 class="text-lg font-semibold">{{ $principle['title'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-white/60">{{ $principle['description'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
