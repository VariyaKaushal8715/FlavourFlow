@props(['intro', 'modules'])

<section id="modules" class="bg-white py-20 sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
        <x-site.section-heading :eyebrow="$intro['eyebrow']" :title="$intro['title']" :description="$intro['description']" />

        <div class="mt-12 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($modules as $module)
                <article class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm shadow-zinc-200/70 transition hover:-translate-y-1 hover:border-red-200 hover:shadow-lg hover:shadow-red-950/10">
                    <p class="text-sm font-semibold text-emerald-700">{{ $module['name'] }}</p>
                    <h3 class="mt-4 text-xl font-semibold leading-7 text-zinc-950">{{ $module['title'] }}</h3>
                    <p class="mt-3 text-sm leading-7 text-zinc-600">{{ $module['description'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
