@props(['company'])

@php
    $factLabels = [
        'Established' => __('ui.established'),
        'Spices and blends' => __('ui.spices_blends'),
        'Orders packed' => __('ui.orders_packed'),
        'Our roots' => __('ui.our_roots'),
    ];

    $principleTitles = [
        'Honest sourcing' => __('ui.honest_sourcing'),
        'Small-batch freshness' => __('ui.small_batch_freshness'),
        'Made for real kitchens' => __('ui.real_kitchens'),
    ];

    $principleDescs = [
        'Honest sourcing' => __('ui.honest_sourcing_desc'),
        'Small-batch freshness' => __('ui.small_batch_freshness_desc'),
        'Made for real kitchens' => __('ui.real_kitchens_desc'),
    ];
@endphp

<section id="company" class="bg-brand-ink py-10 text-white sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 sm:gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:gap-20">
            <div data-reveal>
                <p class="text-xs sm:text-sm font-semibold text-brand-accent">{{ __('ui.company_eyebrow') }}</p>
                <h2 class="mt-2 sm:mt-4 text-2xl sm:text-5xl font-semibold leading-tight">{{ __('ui.company_title') }}</h2>
                <p class="mt-3 sm:mt-6 max-w-xl text-xs sm:text-base leading-relaxed sm:leading-8 text-white/70">{{ __('ui.company_desc') }}</p>
            </div>

            <div class="grid grid-cols-2 border-y border-white/15 sm:grid-cols-4 lg:self-start" data-reveal>
                @foreach ($company['facts'] as $fact)
                    <div class="border-white/15 px-3 py-4 sm:px-4 sm:py-6 not-first:border-l">
                        <p class="text-lg sm:text-2xl font-semibold text-brand-accent">{{ $fact['value'] }}</p>
                        <p class="mt-1 sm:mt-2 text-[0.65rem] sm:text-xs leading-snug sm:leading-5 text-white/55">{{ $factLabels[$fact['label']] ?? $fact['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-8 sm:mt-14 grid gap-6 sm:gap-8 border-t border-white/15 pt-6 sm:pt-10 md:grid-cols-3">
            @foreach ($company['principles'] as $index => $principle)
                <article class="grid grid-cols-[1.5rem_1fr] sm:grid-cols-[2rem_1fr] gap-3 sm:gap-4" data-reveal data-reveal-delay="{{ $index * 90 }}">
                    <span class="text-xs sm:text-sm font-semibold text-brand-accent">0{{ $index + 1 }}</span>
                    <div>
                        <h3 class="text-sm sm:text-lg font-semibold">{{ $principleTitles[$principle['title']] ?? $principle['title'] }}</h3>
                        <p class="mt-1.5 sm:mt-3 text-xs sm:text-sm leading-relaxed sm:leading-7 text-white/60">{{ $principleDescs[$principle['title']] ?? $principle['description'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
