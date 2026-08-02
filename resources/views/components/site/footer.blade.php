@props(['site'])

@php
    $footer = $site['footer'];
    $brand = $site['brand'];
    $contact = $footer['contact'];
    $phoneHref = 'tel:' . preg_replace('/[^\d+]/', '', $contact['phone']);
    $emailHref = 'mailto:' . $contact['email'];
@endphp

<footer class="site-footer relative overflow-hidden border-t border-zinc-200/80 bg-[linear-gradient(180deg,#fffaf1_0%,#ffffff_100%)] text-zinc-700">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(180,35,24,0.12),transparent_28%),radial-gradient(circle_at_top_right,rgba(244,185,66,0.18),transparent_24%),radial-gradient(circle_at_bottom_left,rgba(9,9,11,0.03),transparent_24%)]"></div>

    <div class="relative mx-auto w-full max-w-7xl px-6 py-16 lg:px-8 lg:py-20">
        <div class="grid gap-8 xl:grid-cols-[1.35fr_0.8fr_0.9fr_1.05fr]">
            <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-[0_18px_50px_rgba(17,24,39,0.08)] backdrop-blur-sm lg:p-8" data-reveal>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="absolute inset-0 rounded-2xl bg-brand-primary/15 blur-xl"></div>
                        <img class="relative h-14 w-14 rounded-2xl object-cover ring-1 ring-zinc-200" src="{{ asset($brand['logo']) }}" alt="{{ $brand['name'] }} logo">
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-brand-primary">FlavourFlow</p>
                        <h2 class="mt-1 text-2xl font-semibold text-zinc-950 sm:text-3xl">{{ $brand['name'] }}</h2>
                    </div>
                </div>

                <p class="mt-5 max-w-xl text-base leading-8 text-zinc-600">
                    {{ $footer['brand']['tagline'] }}
                </p>

                <p class="mt-4 max-w-2xl text-sm leading-7 text-zinc-500">
                    {{ $footer['brand']['description'] }}
                </p>

                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <a
                        class="footer-cta-button inline-flex min-h-12 items-center justify-center rounded-full bg-[linear-gradient(135deg,var(--brand-primary)_0%,var(--brand-accent)_100%)] px-5 text-sm font-semibold text-white shadow-[0_14px_30px_rgba(180,35,24,0.22)] transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2"
                        href="{{ $footer['brand']['cta']['href'] }}"
                    >
                        {{ $footer['brand']['cta']['label'] }}
                    </a>

                    <span class="text-sm font-medium text-zinc-400">Fresh from trusted farms across India</span>
                </div>

                <div class="mt-8">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-500">Trust signals</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @foreach ($footer['trust_badges'] as $badge)
                            <span
                                class="footer-mini-badge inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-zinc-200 bg-white text-brand-primary shadow-[0_10px_24px_rgba(17,24,39,0.08)] transition"
                                title="{{ $badge['label'] }}"
                                aria-label="{{ $badge['label'] }}"
                            >
                                <x-site.icon :name="$badge['icon']" class="h-5 w-5" />
                                <span class="sr-only">{{ $badge['label'] }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="mt-8">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-500">Follow us</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @foreach ($footer['socials'] as $social)
                        <a
                                class="footer-social-button inline-flex h-11 w-11 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700 shadow-[0_10px_24px_rgba(17,24,39,0.08)] transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2"
                                href="{{ $social['href'] }}"
                                target="_blank"
                                rel="noreferrer"
                                aria-label="{{ $social['label'] }}"
                                title="{{ $social['label'] }}"
                                style="--social-glow: {{ $social['brand'] }}33; --social-color: {{ $social['brand'] }};"
                            >
                                <x-site.icon :name="$social['icon']" class="h-[1.125rem] w-[1.125rem]" />
                                <span class="sr-only">{{ $social['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="footer-panel" data-reveal data-reveal-delay="90">
                <h3 class="footer-heading">Quick Links</h3>
                <nav class="mt-5 space-y-4" aria-label="Footer quick links">
                    @foreach ($footer['quick_links'] as $link)
                        <a class="footer-link text-sm font-medium text-zinc-600" href="{{ $link['href'] }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>
            </section>

            <section class="footer-panel" data-reveal data-reveal-delay="180">
                <h3 class="footer-heading">Customer Service</h3>
                <nav class="mt-5 space-y-4" aria-label="Customer service links">
                    @foreach ($footer['customer_service'] as $link)
                        <a class="footer-link text-sm font-medium text-zinc-600" href="{{ $link['href'] }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>
            </section>

            <section class="footer-panel" data-reveal data-reveal-delay="270">
                <h3 class="footer-heading">Contact Information</h3>

                <div class="mt-5 space-y-4 text-sm text-zinc-600">
                    <div class="flex gap-3">
                        <span class="footer-inline-icon">
                            <x-site.icon name="pin" class="h-[1.125rem] w-[1.125rem]" />
                        </span>
                        <div>
                            <p class="font-medium text-zinc-900">Address</p>
                            <p class="mt-1 leading-7">{{ $contact['address'] }}</p>
                        </div>
                    </div>

                    <a class="footer-info-link flex gap-3" href="{{ $phoneHref }}">
                        <span class="footer-inline-icon">
                            <x-site.icon name="phone" class="h-[1.125rem] w-[1.125rem]" />
                        </span>
                        <div>
                            <p class="font-medium text-zinc-900">Phone</p>
                            <p class="mt-1 leading-7">{{ $contact['phone'] }}</p>
                        </div>
                    </a>

                    <a class="footer-info-link flex gap-3" href="{{ $emailHref }}">
                        <span class="footer-inline-icon">
                            <x-site.icon name="mail" class="h-[1.125rem] w-[1.125rem]" />
                        </span>
                        <div>
                            <p class="font-medium text-zinc-900">Email</p>
                            <p class="mt-1 break-all leading-7">{{ $contact['email'] }}</p>
                        </div>
                    </a>

                    <div class="flex gap-3">
                        <span class="footer-inline-icon">
                            <x-site.icon name="clock" class="h-[1.125rem] w-[1.125rem]" />
                        </span>
                        <div>
                            <p class="font-medium text-zinc-900">Working Hours</p>
                            <p class="mt-1 leading-7">{{ $contact['hours'][0] }}</p>
                            <p class="leading-7">{{ $contact['hours'][1] }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="mt-12 rounded-[2rem] border border-zinc-200/80 bg-white/85 p-5 shadow-[0_16px_40px_rgba(17,24,39,0.06)] backdrop-blur-sm sm:p-6" data-reveal data-reveal-delay="360">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-500">Secure payments</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @foreach ($footer['payments'] as $payment)
                            <span
                                class="footer-payment-badge inline-flex h-11 items-center justify-center rounded-2xl border border-zinc-200 bg-white px-3 text-zinc-600 shadow-[0_10px_24px_rgba(17,24,39,0.06)] transition"
                                title="{{ $payment['label'] }}"
                                aria-label="{{ $payment['label'] }}"
                            >
                                <x-site.icon :name="$payment['icon']" class="h-5 w-auto" />
                                <span class="sr-only">{{ $payment['label'] }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <p class="text-sm text-zinc-500">
                        {{ $footer['copyright'] }}
                    </p>
                    <button
                        class="back-to-top group inline-flex h-12 w-12 items-center justify-center self-start rounded-full border border-zinc-200 bg-white text-zinc-700 shadow-[0_12px_28px_rgba(17,24,39,0.10)] transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2"
                        type="button"
                        data-back-to-top
                        aria-label="Back to top"
                        tabindex="-1"
                    >
                        <x-site.icon name="chevron-up" class="h-5 w-5 transition group-hover:-translate-y-0.5" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</footer>
