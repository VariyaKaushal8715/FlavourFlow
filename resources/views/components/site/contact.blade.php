@props(['contact'])

<section id="contact" class="bg-white py-20 sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
        <div class="grid gap-10 rounded-lg border border-zinc-200 bg-zinc-50 p-6 sm:p-8 lg:grid-cols-[1fr_auto] lg:items-center">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold text-brand-primary">{{ $contact['eyebrow'] }}</p>
                <h2 class="mt-3 text-3xl font-semibold leading-tight text-zinc-950 sm:text-4xl">{{ $contact['title'] }}</h2>
                <p class="mt-4 text-base leading-8 text-zinc-600 sm:text-lg">{{ $contact['description'] }}</p>
                <a class="mt-5 inline-flex break-all text-base font-semibold text-emerald-700 transition hover:text-emerald-900" href="mailto:{{ $contact['email'] }}">
                    {{ $contact['email'] }}
                </a>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                <a class="inline-flex min-h-12 items-center justify-center rounded-lg bg-zinc-950 px-5 text-sm font-semibold text-white transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-950 focus:ring-offset-2" href="{{ $contact['primary_action']['href'] }}">
                    {{ $contact['primary_action']['label'] }}
                </a>
                <a class="inline-flex min-h-12 items-center justify-center rounded-lg border border-zinc-300 bg-white px-5 text-sm font-semibold text-zinc-800 transition hover:border-zinc-400 hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-950 focus:ring-offset-2" href="{{ $contact['secondary_action']['href'] }}">
                    {{ $contact['secondary_action']['label'] }}
                </a>
            </div>
        </div>
    </div>
</section>
