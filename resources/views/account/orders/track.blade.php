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
                <p class="mt-2 text-base leading-7 text-zinc-600">Order ID: <span class="font-bold text-zinc-950">{{ $order->order_id }}</span></p>
            </div>
        </div>
    </section>

    <section class="bg-[linear-gradient(180deg,#fff_0%,#fff9ed_48%,#fff_100%)] py-12 sm:py-16">
        <div class="mx-auto w-full max-w-5xl px-6 lg:px-8">
            <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)] sm:p-8" data-reveal>
                <!-- Delivery Header -->
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-100 pb-5">
                    <div>
                        <h2 class="text-xl font-semibold text-zinc-950">Delivery Progress</h2>
                        <p class="mt-1 text-xs text-zinc-500">
                            Current Status: 
                            <span @class([
                                'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold shadow-sm ml-1',
                                'bg-emerald-100 text-emerald-800 border border-emerald-200' => $order->status === 'Confirmed' || $order->status === 'Delivered',
                                'bg-amber-100 text-amber-800 border border-amber-200' => $order->status === 'Shipped' || $order->status === 'Out for Delivery' || $order->status === 'Processing',
                            ])>
                                <span @class([
                                    'h-1.5 w-1.5 rounded-full',
                                    'bg-emerald-600' => $order->status === 'Confirmed' || $order->status === 'Delivered',
                                    'bg-amber-600 animate-ping' => $order->status === 'Shipped' || $order->status === 'Out for Delivery' || $order->status === 'Processing',
                                ])></span>
                                {{ $order->status }}
                            </span>
                        </p>
                    </div>
                    <a href="{{ route('account.orders.show', $order->order_id) }}" class="rounded-xl border border-zinc-300 bg-white px-4 py-2 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-950">
                        View Details
                    </a>
                </div>

                <!-- Desktop Horizontal Stepper (visible on md screens and larger) -->
                <div class="mt-10 hidden md:block">
                    <!-- Progress Node Bar -->
                    <div class="relative flex items-center justify-between px-4">
                        <!-- Horizontal Connecting Lines -->
                        <div class="absolute left-8 right-8 top-1/2 -translate-y-1/2 h-1 bg-zinc-200 -z-0">
                            @php
                                $statusSeq = ['Confirmed', 'Processing', 'Shipped', 'Out for Delivery', 'Delivered'];
                                $activeIdx = array_search($order->status, $statusSeq, true);
                                if ($activeIdx === false) $activeIdx = 0;
                                $pct = ($activeIdx / (count($statusSeq) - 1)) * 100;
                            @endphp
                            <div class="h-full bg-gradient-to-r from-emerald-500 to-amber-500 transition-all duration-500" style="width: {{ $pct }}%"></div>
                        </div>

                        @foreach($steps as $index => $step)
                            <div class="relative z-10 flex flex-col items-center">
                                <div @class([
                                    'flex h-10 w-10 items-center justify-center rounded-full transition-all duration-300 shadow-md',
                                    'bg-emerald-600 text-white ring-4 ring-emerald-100' => $step['state'] === 'completed',
                                    'bg-amber-500 text-white ring-4 ring-amber-100 animate-pulse scale-110' => $step['state'] === 'active',
                                    'bg-white text-zinc-400 border-2 border-zinc-300 ring-2 ring-white' => $step['state'] === 'pending',
                                ])>
                                    @if($step['state'] === 'completed')
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @elseif($step['state'] === 'active')
                                        <span class="h-3 w-3 rounded-full bg-white shadow"></span>
                                    @else
                                        <span class="text-xs font-bold">{{ $index + 1 }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Step Text Labels Grid -->
                    <div class="mt-6 grid grid-cols-5 gap-3 text-center">
                        @foreach($steps as $index => $step)
                            <div class="flex flex-col items-center px-1">
                                <h3 @class([
                                    'text-xs font-bold tracking-tight',
                                    'text-emerald-700' => $step['state'] === 'completed',
                                    'text-amber-600 font-extrabold' => $step['state'] === 'active',
                                    'text-zinc-400' => $step['state'] === 'pending',
                                ])>
                                    {{ $step['label'] }}
                                </h3>
                                @if($step['time'])
                                    <span class="mt-1 text-[11px] font-medium text-zinc-400">
                                        {{ $step['time']->format('M d, Y') }}
                                        <br>
                                        {{ $step['time']->format('h:i A') }}
                                    </span>
                                @endif
                                <p @class([
                                    'mt-1.5 text-[11px] leading-snug',
                                    'text-zinc-600' => $step['state'] !== 'pending',
                                    'text-zinc-400' => $step['state'] === 'pending',
                                ])>
                                    {{ $step['description'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Mobile Vertical Stepper (visible on small screens) -->
                <div class="mt-8 block space-y-6 md:hidden">
                    @foreach($steps as $index => $step)
                        <div class="relative flex items-start gap-4">
                            <!-- Connector line -->
                            @if(! $loop->last)
                                <div @class([
                                    'absolute left-4 top-8 bottom-0 w-0.5 -ml-px',
                                    'bg-emerald-500' => $step['state'] === 'completed',
                                    'bg-zinc-200' => $step['state'] !== 'completed',
                                ])></div>
                            @endif

                            <!-- Dot Icon -->
                            <div @class([
                                'relative z-10 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full shadow-sm',
                                'bg-emerald-600 text-white ring-4 ring-emerald-50' => $step['state'] === 'completed',
                                'bg-amber-500 text-white ring-4 ring-amber-50 animate-pulse' => $step['state'] === 'active',
                                'bg-white text-zinc-400 border-2 border-zinc-300' => $step['state'] === 'pending',
                            ])>
                                @if($step['state'] === 'completed')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                @elseif($step['state'] === 'active')
                                    <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                                @else
                                    <span class="text-xs font-bold">{{ $index + 1 }}</span>
                                @endif
                            </div>

                            <!-- Content Card -->
                            <div @class([
                                'flex-1 rounded-2xl border p-4 shadow-sm transition',
                                'border-emerald-200 bg-emerald-50/30' => $step['state'] === 'completed',
                                'border-amber-200 bg-amber-50/40 ring-1 ring-amber-200' => $step['state'] === 'active',
                                'border-zinc-200 bg-white opacity-70' => $step['state'] === 'pending',
                            ])>
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h3 @class([
                                        'text-sm font-bold',
                                        'text-emerald-800' => $step['state'] === 'completed',
                                        'text-amber-800' => $step['state'] === 'active',
                                        'text-zinc-500' => $step['state'] === 'pending',
                                    ])>
                                        {{ $step['label'] }}
                                    </h3>
                                    @if($step['time'])
                                        <span class="text-xs font-medium text-zinc-400">
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
