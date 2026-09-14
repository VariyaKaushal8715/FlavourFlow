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

                <!-- Vertical timeline connecting all steps -->
                <div class="mt-8 space-y-0">
                    @foreach($steps as $index => $step)
                        @php
                            $isLast = $loop->last;
                            $isCompleted = $step['state'] === 'completed';
                            $isActive = $step['state'] === 'active';
                            $isPending = $step['state'] === 'pending';
                            $isCancelled = $step['name'] === 'Cancelled';
                        @endphp

                        <div class="relative flex gap-4 sm:gap-6">
                            <!-- Timeline Node (Dot + Line) -->
                            <div class="flex flex-col items-center">
                                <!-- Dot -->
                                <div @class([
                                    'relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full transition-all duration-200',
                                    'bg-emerald-600 text-white ring-4 ring-emerald-50 shadow-sm' => $isCompleted,
                                    'bg-amber-500 text-white ring-4 ring-amber-100 shadow-sm' => $isActive && !$isCancelled,
                                    'bg-rose-600 text-white ring-4 ring-rose-100 shadow-sm' => $isActive && $isCancelled,
                                    'bg-white border-2 border-zinc-300 text-zinc-300 ring-4 ring-zinc-50' => $isPending,
                                ])>
                                    @if($isCompleted)
                                        <svg class="h-3.5 w-3.5 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @elseif($isActive)
                                        @if($isCancelled)
                                            <svg class="h-3.5 w-3.5 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        @else
                                            <span class="relative flex h-2.5 w-2.5">
                                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-75"></span>
                                                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-white"></span>
                                            </span>
                                        @endif
                                    @else
                                        <span class="h-2 w-2 rounded-full bg-zinc-300"></span>
                                    @endif
                                </div>

                                <!-- Connecting Line to next step -->
                                @if(!$isLast)
                                    <div @class([
                                        'w-0.5 flex-1 my-1',
                                        'bg-emerald-500' => $isCompleted,
                                        'bg-gradient-to-b from-amber-500 to-zinc-200' => $isActive && !$isCancelled,
                                        'bg-rose-300' => $isActive && $isCancelled,
                                        'bg-zinc-200' => $isPending,
                                    ])></div>
                                @endif
                            </div>

                            <!-- Content Column -->
                            <div @class([
                                'flex-1 min-w-0',
                                'pb-8' => !$isLast,
                                'pb-1' => $isLast,
                            ])>
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 sm:gap-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 @class([
                                            'text-sm sm:text-base font-bold tracking-tight',
                                            'text-zinc-950' => $isCompleted,
                                            'text-amber-900' => $isActive && !$isCancelled,
                                            'text-rose-950' => $isActive && $isCancelled,
                                            'text-zinc-400 font-medium' => $isPending,
                                        ])>
                                            {{ $step['label'] }}
                                        </h3>

                                        @if($isActive && !$isCancelled)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-semibold text-amber-800 ring-1 ring-inset ring-amber-500/20">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Current Status
                                            </span>
                                        @elseif($isActive && $isCancelled)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-[11px] font-semibold text-rose-800 ring-1 ring-inset ring-rose-500/20">
                                                Cancelled
                                            </span>
                                        @elseif($isCompleted)
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                Completed
                                            </span>
                                        @endif
                                    </div>

                                    @if($step['time'])
                                        <span class="text-xs font-medium text-zinc-400 sm:text-right shrink-0">
                                            {{ $step['time']->format('M d, Y h:i A') }}
                                        </span>
                                    @endif
                                </div>

                                <p @class([
                                    'mt-1 text-xs sm:text-sm leading-relaxed',
                                    'text-zinc-600' => !$isPending,
                                    'text-zinc-400' => $isPending,
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

