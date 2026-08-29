<x-site.layout :site="$site" page-title="Track Order | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    <section class="border-b border-amber-200/60 bg-[radial-gradient(circle_at_top_left,rgba(244,185,66,0.24),transparent_34%),linear-gradient(135deg,#fff9ed_0%,#fff_52%,#fff3df_100%)] py-10 sm:py-14">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <div class="max-w-3xl" data-reveal>
                <div class="flex items-center gap-2">
                    <a href="{{ route('account.orders') }}" class="text-xs font-semibold text-brand-primary hover:underline">&larr; Back to My Orders</a>
                </div>
                <h1 class="mt-4 text-3xl font-semibold text-zinc-950 sm:text-4xl">Track Order</h1>
                <p class="mt-2 text-base leading-7 text-zinc-600">Order ID: <span class="font-bold text-zinc-950">{{ $order->order_number }}</span></p>
            </div>
        </div>
    </section>

    <section class="bg-[linear-gradient(180deg,#fff_0%,#fff9ed_48%,#fff_100%)] py-12 sm:py-16">
        <div class="mx-auto w-full max-w-3xl px-6 lg:px-8">
            <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)] sm:p-8" data-reveal>
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-100 pb-5">
                    <div>
                        <h2 class="text-xl font-semibold text-zinc-950">Delivery Progress</h2>
                        <p class="mt-1 text-xs text-zinc-500">Current Status: <span class="font-semibold text-brand-primary">{{ $order->status }}</span></p>
                    </div>
                    <a href="{{ route('account.orders.show', $order->order_number) }}" class="rounded-xl border border-zinc-300 bg-white px-4 py-2 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50">
                        View Details
                    </a>
                </div>

                <!-- Vertical timeline for mobile, styled cleanly -->
                <div class="mt-8 relative pl-8 space-y-8 before:absolute before:left-3.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-zinc-200">
                    @foreach($steps as $index => $step)
                        <div class="relative">
                            <!-- Dot indicators -->
                            <span @class([
                                'absolute -left-[27px] top-1.5 flex h-5 w-5 items-center justify-center rounded-full ring-4 ring-white',
                                'bg-emerald-600 text-white' => $step['state'] === 'completed',
                                'bg-amber-500 text-white animate-pulse' => $step['state'] === 'active',
                                'bg-zinc-200 text-zinc-400' => $step['state'] === 'pending',
                            ])>
                                @if($step['state'] === 'completed')
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                @elseif($step['state'] === 'active')
                                    <span class="h-2 w-2 rounded-full bg-white"></span>
                                @endif
                            </span>

                            <!-- Step content -->
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h3 @class([
                                        'text-sm font-semibold',
                                        'text-emerald-700' => $step['state'] === 'completed',
                                        'text-amber-600' => $step['state'] === 'active',
                                        'text-zinc-400' => $step['state'] === 'pending',
                                    ])>
                                        {{ $step['label'] }}
                                    </h3>
                                    @if($step['time'])
                                        <span class="text-xs text-zinc-400 font-medium">
                                            {{ $step['time']->format('M d, Y h:i A') }}
                                        </span>
                                    @endif
                                </div>
                                <p @class([
                                    'mt-1 text-xs leading-relaxed',
                                    'text-zinc-600' => $step['state'] !== 'pending',
                                    'text-zinc-400' => $step['state'] === 'pending',
                                ])>
                                    {{ $step['description'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</x-site.layout>

