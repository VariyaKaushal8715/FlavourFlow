@props(['process'])

<section id="process" class="bg-[#f7faf5] py-20 sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
        <x-site.section-heading :eyebrow="$process['eyebrow']" :title="$process['title']" :description="$process['description']" />

        <div class="mt-12 grid gap-4 lg:grid-cols-3">
            @foreach ($process['steps'] as $step)
                <article class="rounded-lg border border-emerald-900/10 bg-white p-6 shadow-sm shadow-emerald-950/5">
                    <p class="text-sm font-semibold text-brand-primary">{{ $step['number'] }}</p>
                    <h3 class="mt-4 text-xl font-semibold leading-7 text-zinc-950">{{ $step['title'] }}</h3>
                    <p class="mt-3 text-sm leading-7 text-zinc-600">{{ $step['description'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
