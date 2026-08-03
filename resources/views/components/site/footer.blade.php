@props(['site'])

@php
    $footer = $site['footer'];
    $brand = $site['brand'];
    $contact = $footer['contact'];
    $phoneHref = 'tel:' . preg_replace('/[^\d+]/', '', $contact['phone']);
    $emailHref = 'mailto:' . $contact['email'];
@endphp

<footer class="site-footer relative overflow-hidden bg-zinc-950 text-zinc-300">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(180,35,24,0.26),transparent_30%),radial-gradient(circle_at_top_right,rgba(244,185,66,0.16),transparent_24%),linear-gradient(180deg,rgba(9,9,11,0.92)_0%,rgba(9,9,11,1)_58%)]"></div>
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-amber-300/40 to-transparent"></div>

    <div class="relative mx-auto w-full max-w-7xl px-6 py-16 lg:px-8 lg:py-20">
        <div class="grid gap-8 lg:grid-cols-[1.25fr_1fr]">
            <section class="footer-surface rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.24)] backdrop-blur-sm lg:p-8" data-reveal>
                <div class="flex items-start gap-4">
                    <img class="footer-logo h-10 w-10 rounded-xl object-cover ring-1 ring-white/10" src="{{ asset($brand['logo']) }}" alt="{{ $brand['name'] }} logo">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">FlavourFlow</p>
                        <h2 class="mt-2 text-2xl font-semibold text-white sm:text-3xl">{{ $brand['name'] }}</h2>
                        <p class="mt-2 text-sm leading-6 text-zinc-400">{{ $footer['brand']['tagline'] }}</p>
                    </div>
                </div>

                <p class="mt-6 max-w-xl text-sm leading-7 text-zinc-400">
                    {{ $footer['brand']['description'] }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a class="inline-flex min-h-12 items-center justify-center rounded-full bg-gradient-to-r from-red-700 to-amber-500 px-5 text-sm font-semibold text-white shadow-[0_16px_34px_rgba(180,35,24,0.28)] transition hover:translate-y-[-1px] hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300" href="{{ $footer['brand']['cta']['href'] }}">
                        {{ $footer['brand']['cta']['label'] }}
                    </a>
                        <span class="inline-flex min-h-12 items-center justify-center rounded-full border border-white/10 bg-white/5 px-4 text-xs font-semibold uppercase tracking-[0.22em] text-amber-200/90">
                            Premium spice pantry
                        </span>
                </div>

                <div class="mt-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-zinc-500">Trust signals</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @foreach ($footer['trust_badges'] as $index => $badge)
                            <button
                                class="trust-badge inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-amber-300 shadow-[0_10px_24px_rgba(0,0,0,0.18)] transition hover:-translate-y-0.5 hover:border-amber-300/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300"
                                type="button"
                                data-trust-badge
                                data-trust-title="{{ $badge['title'] }}"
                                data-trust-description="{{ $badge['description'] }}"
                                data-trust-label="{{ $badge['label'] }}"
                                data-trust-index="{{ $index }}"
                                aria-label="{{ $badge['label'] }}"
                            >
                                <x-site.icon :name="$badge['icon']" class="h-5 w-5" />
                                <span class="sr-only">{{ $badge['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="rounded-[1.5rem] border border-white/10 bg-white/[0.03] p-6 backdrop-blur-sm lg:p-8" data-reveal data-reveal-delay="90">
                <h3 class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-300">Contact</h3>

                <div class="mt-5 space-y-4 text-sm text-zinc-300">
                    <div class="flex gap-3">
                        <span class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-amber-300">
                            <x-site.icon name="pin" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="font-medium text-white">Address</p>
                            <p class="mt-1 leading-7 text-zinc-400">{{ $contact['address'] }}</p>
                        </div>
                    </div>

                    <a class="flex gap-3 transition hover:translate-x-1 hover:text-white" href="{{ $phoneHref }}">
                        <span class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-amber-300">
                            <x-site.icon name="phone" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="font-medium text-white">Phone</p>
                            <p class="mt-1 leading-7 text-zinc-400">{{ $contact['phone'] }}</p>
                        </div>
                    </a>

                    <a class="flex gap-3 transition hover:translate-x-1 hover:text-white" href="{{ $emailHref }}">
                        <span class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-amber-300">
                            <x-site.icon name="mail" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="font-medium text-white">Email</p>
                            <p class="mt-1 break-all leading-7 text-zinc-400">{{ $contact['email'] }}</p>
                        </div>
                    </a>

                    <div class="flex gap-3">
                        <span class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-amber-300">
                            <x-site.icon name="clock" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="font-medium text-white">Working hours</p>
                            <p class="mt-1 leading-7 text-zinc-400">{{ $contact['hours'][0] }}</p>
                            <p class="leading-7 text-zinc-400">{{ $contact['hours'][1] }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="mt-10 flex flex-col gap-3 rounded-[1.5rem] border border-white/10 bg-white/[0.04] p-5 backdrop-blur-sm sm:flex-row sm:items-center sm:justify-between" data-reveal data-reveal-delay="180">
            <p class="text-sm text-zinc-500">{{ $footer['copyright'] }}</p>
            <button
                class="back-to-top group inline-flex h-12 w-12 items-center justify-center self-start rounded-full border border-white/10 bg-white/5 text-white transition hover:border-amber-300/30 hover:bg-white/10"
                type="button"
                data-back-to-top
                aria-label="Back to top"
                tabindex="-1"
            >
                <x-site.icon name="chevron-up" class="h-5 w-5 transition group-hover:-translate-y-0.5" />
            </button>
        </div>
    </div>

    <div
        class="trust-overlay fixed inset-0 z-50 hidden items-center justify-center bg-black/70 px-4 py-8"
        data-trust-dialog
        aria-hidden="true"
    >
        <div
            class="w-[min(92vw,28rem)] rounded-[1.5rem] border border-white/10 bg-zinc-950 text-zinc-100 shadow-[0_24px_80px_rgba(0,0,0,0.5)]"
            role="dialog"
            aria-modal="true"
            aria-labelledby="trust-dialog-title"
            aria-describedby="trust-dialog-description"
        >
            <div class="border-b border-white/10 px-6 py-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-300">Trust signal</p>
                        <h3 id="trust-dialog-title" class="mt-2 text-2xl font-semibold text-white"></h3>
                    </div>
                    <button
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white transition hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300"
                        type="button"
                        data-trust-close
                        aria-label="Close trust signal details"
                    >
                        <span class="text-xl leading-none">×</span>
                    </button>
                </div>
            </div>
            <div class="px-6 py-5">
                <p id="trust-dialog-description" class="text-sm leading-7 text-zinc-300"></p>
                <div class="mt-5 flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.22em] text-amber-300">
                    <x-site.icon name="shield-check" class="h-4 w-4" />
                    <span data-trust-label></span>
                </div>
            </div>
        </div>
    </div>
</footer>
