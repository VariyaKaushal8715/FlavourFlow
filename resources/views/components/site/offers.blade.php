@props(['offers'])

@if ($offers->isNotEmpty())
    <section id="offers" class="bg-white py-20 sm:py-24">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <div class="grid gap-6 border-b border-zinc-200 pb-9 lg:grid-cols-[minmax(0,1fr)_minmax(20rem,0.65fr)] lg:items-end" data-reveal>
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold text-brand-primary">Worth opening today</p>
                    <h2 class="mt-3 text-3xl font-semibold leading-tight text-zinc-950 sm:text-5xl">Special offers, made timely.</h2>
                </div>
                <p class="max-w-lg text-base leading-8 text-zinc-600 lg:justify-self-end">Seasonal bundles and limited-value picks, kept clear so you always know what you are getting.</p>
            </div>

            <div class="offer-stage group relative mt-10 min-h-[42rem] overflow-hidden rounded-lg bg-zinc-950 text-white sm:min-h-[40rem] lg:min-h-[38rem]" data-offer-stage data-reveal>
                <img class="offer-stage-image absolute inset-0 h-full w-full object-cover" src="{{ asset('images/flavourflow-offers-composite.png') }}" alt="FlavourFlow spices and brand mark">
                <div class="offer-stage-shade absolute inset-0" aria-hidden="true"></div>
                <div class="offer-stage-glow absolute left-0 top-0 h-full w-2/3" aria-hidden="true"></div>

                <div class="relative z-10 min-h-[42rem] sm:min-h-[40rem] lg:min-h-[38rem]">
                    @foreach ($offers as $index => $offer)
                        @php
                            $motionName = strtolower($offer->eyebrow.' '.$offer->title);
                            $motion = match (true) {
                                str_contains($motionName, 'monsoon'), str_contains($motionName, 'rain') => 'monsoon',
                                str_contains($motionName, 'today') => 'today',
                                str_contains($motionName, 'festival'), str_contains($motionName, 'celebrate') => 'festival',
                                default => ['slide', 'rise', 'focus'][$index % 3],
                            };
                        @endphp

                        <article
                            id="offer-panel-{{ $offer->id }}"
                            @class([
                                'offer-panel absolute inset-x-0 top-0 max-w-2xl px-6 pb-40 pt-10 sm:px-10 sm:pb-36 sm:pt-14 lg:px-14 lg:pb-40 lg:pt-16',
                                'is-active' => $loop->first,
                            ])
                            data-offer-panel
                            data-offer-motion="{{ $motion }}"
                            aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                        >
                            <a class="block rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:ring-offset-4 focus:ring-offset-zinc-950" href="{{ route('offers.show', $offer) }}" aria-label="View {{ $offer->title }} offer details">
                                <div class="flex flex-wrap items-center gap-4">
                                    <span class="offer-discount relative inline-flex min-h-16 min-w-28 items-center justify-center overflow-hidden rounded-lg bg-brand-accent px-5 py-3 text-xl font-semibold text-brand-ink shadow-lg shadow-black/25 sm:text-2xl">
                                        {{ $offer->discount_label }}
                                    </span>
                                    <span class="offer-eyebrow text-xs font-semibold uppercase text-brand-accent">{{ $offer->eyebrow }}</span>
                                </div>

                                <h3 class="offer-panel-title mt-7 max-w-xl text-4xl font-semibold leading-tight sm:text-5xl">{{ $offer->title }}</h3>
                                <p class="offer-panel-description mt-5 max-w-xl text-base leading-8 text-white/75">{{ $offer->description }}</p>

                                <div class="offer-panel-meta mt-7 flex flex-wrap items-center gap-3 text-xs font-semibold">
                                    @if ($offer->coupon_code)
                                        <span class="rounded-lg border border-white/25 bg-black/35 px-4 py-3 backdrop-blur">Use code: {{ $offer->coupon_code }}</span>
                                    @endif
                                    <span class="text-white/60">{{ $offer->dateRangeLabel() }}</span>
                                    <span class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-3 text-zinc-950">
                                        View offer details <span aria-hidden="true">&rarr;</span>
                                    </span>
                                </div>
                            </a>
                        </article>
                    @endforeach

                    <div class="offer-tabs absolute inset-x-4 bottom-4 z-20 flex gap-2 overflow-x-auto rounded-lg border border-white/15 bg-black/45 p-2 backdrop-blur-xl sm:inset-x-6 sm:bottom-6 lg:inset-x-10" role="tablist" aria-label="Available offers">
                        @foreach ($offers as $offer)
                            <a
                                class="offer-tab min-w-[12rem] flex-1 rounded-md px-4 py-3 text-left transition focus:outline-none focus:ring-2 focus:ring-brand-accent"
                                href="{{ route('offers.show', $offer) }}"
                                role="tab"
                                aria-controls="offer-panel-{{ $offer->id }}"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                data-offer-tab
                            >
                                <span class="flex items-center justify-between gap-3">
                                    <span class="truncate text-xs font-semibold text-white/65">{{ $offer->eyebrow }}</span>
                                    <span class="offer-tab-value shrink-0 text-xs font-semibold text-brand-accent">{{ $offer->discount_label }}</span>
                                </span>
                                <span class="mt-2 block truncate text-sm font-semibold text-white">{{ $offer->title }}</span>
                                <span class="offer-tab-progress mt-3 block h-0.5 overflow-hidden bg-white/15" aria-hidden="true">
                                    <span class="block h-full w-full origin-left scale-x-0 bg-brand-accent"></span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
