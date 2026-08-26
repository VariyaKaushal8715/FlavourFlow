<x-admin.layout title="AI Engine Diagnostics">
    <div class="space-y-6">
        <!-- Page Title & Overall Readiness Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-bold uppercase tracking-wider text-amber-800">
                        Step 3 - Context & Analysis Verification
                    </span>
                </div>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">
                    AI Engine Health & Analysis Layer
                </h1>
                <p class="mt-1 text-sm text-zinc-500">
                    Automated, non-fake verification of AI Core, Event Tracking, Context Builder, and Pattern Analysis Engine.
                </p>
            </div>

            <!-- Overall System Readiness Indicator -->
            <div class="flex items-center gap-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                <div class="text-right">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Overall System Status</p>
                    <p class="text-sm font-bold {{ $isReady ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $isReady ? 'Ready for Operation' : 'Action Required' }}
                    </p>
                </div>
                <span @class([
                    'inline-flex h-10 px-4 items-center justify-center rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm',
                    'bg-emerald-600 text-white' => $isReady,
                    'bg-rose-600 text-white' => ! $isReady,
                ])>
                    {{ $isReady ? 'Ready' : 'Not Ready' }}
                </span>
            </div>
        </div>

        <!-- Temporary Isolated Notice Banner -->
        <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 text-amber-900 shadow-sm">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div class="text-xs leading-relaxed">
                    <p class="font-bold">Development & Diagnostic Isolation Notice (Step 3 - Context & Analysis Layer)</p>
                    <p class="mt-0.5 text-amber-800">
                        This AI Engine status page and sidebar navigation item are isolated for step 3 verification. All context extraction and behavioral pattern analysis run deterministically over real database events without calling external APIs.
                    </p>
                </div>
            </div>
        </div>

        <!-- Verification Diagnostic Checks Grid -->
        <div>
            <h2 class="text-base font-bold text-zinc-950 mb-3">System, Event & Analysis Verification Checks</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-2">
                @foreach($checks as $key => $check)
                    <div @class([
                        'rounded-2xl border p-5 transition shadow-sm bg-white',
                        'border-zinc-200 hover:border-zinc-300' => $check['passed'],
                        'border-rose-300 bg-rose-50/20' => ! $check['passed'],
                    ])>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-bold text-zinc-950">{{ $check['name'] }}</h3>
                                <p class="mt-1 text-xs text-zinc-500 leading-relaxed">{{ $check['description'] }}</p>
                            </div>
                            <span @class([
                                'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold shadow-sm flex-shrink-0',
                                'bg-emerald-100 text-emerald-800 border border-emerald-200' => $check['passed'],
                                'bg-rose-100 text-rose-800 border border-rose-200' => ! $check['passed'],
                            ])>
                                <span>{{ $check['passed'] ? '✓' : '✕' }}</span>
                                <span>{{ $check['passed'] ? 'Verified' : 'Failed' }}</span>
                            </span>
                        </div>

                        <div class="mt-4 rounded-xl border border-zinc-100 bg-zinc-50 p-3 text-[11px] font-mono text-zinc-700 break-all">
                            {{ $check['details'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Step 3 Live Analysis & Insights Showcase Grid -->
        @if(! empty($sampleAnalysis))
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-4">
                    <div>
                        <h2 class="text-base font-bold text-zinc-950">Live Behavioral Insights (Step 3 Analysis Output)</h2>
                        <p class="mt-0.5 text-xs text-zinc-500">Deterministic pattern detection produced by <code class="font-mono text-brand-primary">AiAnalyzerInterface</code> from current DB events.</p>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                        Deterministically Generated
                    </span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Purchase Intent Card -->
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Purchase Intent</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-black text-zinc-950 uppercase">{{ $sampleAnalysis['purchase_intent']['level'] ?? 'none' }}</span>
                            <span class="text-xs font-bold text-emerald-600">({{ $sampleAnalysis['purchase_intent']['score'] ?? 0 }}/100)</span>
                        </div>
                        <p class="mt-1 text-[11px] text-zinc-500 leading-snug">{{ $sampleAnalysis['purchase_intent']['rationale'] ?? '' }}</p>
                    </div>

                    <!-- Cart Abandonment Risk Card -->
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Cart Abandonment Signal</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span @class([
                                'text-2xl font-black uppercase',
                                'text-amber-600' => ($sampleAnalysis['cart_abandonment']['risk_level'] ?? 'none') === 'medium',
                                'text-rose-600' => ($sampleAnalysis['cart_abandonment']['risk_level'] ?? 'none') === 'high',
                                'text-zinc-500' => ($sampleAnalysis['cart_abandonment']['risk_level'] ?? 'none') === 'none',
                            ])>
                                {{ $sampleAnalysis['cart_abandonment']['risk_level'] ?? 'none' }}
                            </span>
                        </div>
                        <p class="mt-1 text-[11px] text-zinc-500 leading-snug">
                            {{ ($sampleAnalysis['cart_abandonment']['is_abandoned'] ?? false) ? 'Unpurchased items present' : 'No cart abandonment detected' }}
                        </p>
                    </div>

                    <!-- Top Category Preference Card -->
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Preferred Category</p>
                        <div class="mt-2">
                            <span class="text-lg font-bold text-zinc-950 truncate block">
                                {{ $sampleAnalysis['category_preference']['top_category'] ?? 'None yet' }}
                            </span>
                        </div>
                        <p class="mt-1 text-[11px] text-zinc-500">Based on view & cart interaction weighting</p>
                    </div>

                    <!-- Recommendation Trigger Card -->
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Recommendation Strategy</p>
                        <div class="mt-2">
                            <span class="text-xs font-mono font-bold text-purple-700 bg-purple-100 px-2 py-1 rounded inline-block">
                                {{ $sampleAnalysis['recommendation_signals']['trigger'] ?? 'general_browse' }}
                            </span>
                        </div>
                        <p class="mt-1.5 text-[11px] text-zinc-500">Automated seed rule</p>
                    </div>
                </div>

                <!-- Structured Context Payload Inspection -->
                <div class="rounded-xl border border-zinc-100 bg-zinc-950 p-4 text-white">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-mono text-amber-400 font-bold">Structured User Context Payload (AiContextBuilder)</span>
                        <span class="text-[10px] font-mono text-zinc-400">Total Events Analyzed: {{ $sampleContext['total_events'] ?? 0 }}</span>
                    </div>
                    <pre class="text-[11px] font-mono text-zinc-300 overflow-x-auto p-2 bg-black/50 rounded max-h-60">{{ json_encode($sampleContext, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        @endif

        <!-- Event Types Summary Grid -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-zinc-950">Tracked Event Types & Volumes</h2>
            <p class="mt-1 text-xs text-zinc-500">Live count of recorded events grouped by action type in <code class="font-mono text-brand-primary">ai_events</code> table.</p>

            <div class="mt-4 grid gap-3 grid-cols-2 sm:grid-cols-3 lg:grid-cols-5">
                @php
                    $trackedTypes = [
                        'product_viewed' => 'Product View',
                        'product_searched' => 'Product Search',
                        'category_viewed' => 'Category View',
                        'wishlist_added' => 'Wishlist Add',
                        'wishlist_removed' => 'Wishlist Remove',
                        'cart_added' => 'Cart Add',
                        'cart_removed' => 'Cart Remove',
                        'checkout_started' => 'Checkout Start',
                        'order_placed' => 'Order Placed',
                    ];
                @endphp

                @foreach($trackedTypes as $type => $label)
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">{{ $label }}</p>
                        <div class="mt-1 flex items-baseline justify-between">
                            <span class="text-lg font-bold text-zinc-950">{{ $eventCounts[$type] ?? 0 }}</span>
                            <span class="text-[10px] font-mono text-zinc-500">{{ $type }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Recorded AI Events Log -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-zinc-950">Live Recorded User Events Log</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">Recent real user actions captured via <code class="font-mono text-brand-primary">AiEventTrackerInterface</code>.</p>
                </div>
                <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-600">
                    Showing latest {{ $recentEvents->count() }} events
                </span>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-600">
                    <thead class="bg-zinc-50 text-[10px] uppercase font-bold text-zinc-400 tracking-wider">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Time</th>
                            <th class="px-4 py-3">Event Type</th>
                            <th class="px-4 py-3">User / Session</th>
                            <th class="px-4 py-3">Entity</th>
                            <th class="px-4 py-3 rounded-r-lg">Metadata Payload</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($recentEvents as $event)
                            <tr class="hover:bg-zinc-50/60 transition">
                                <td class="px-4 py-3 font-mono text-zinc-500 whitespace-nowrap">
                                    {{ $event->created_at->format('M d, H:i:s') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold',
                                        'bg-purple-100 text-purple-800' => str_contains($event->event_type, 'product'),
                                        'bg-blue-100 text-blue-800' => str_contains($event->event_type, 'category') || str_contains($event->event_type, 'search'),
                                        'bg-pink-100 text-pink-800' => str_contains($event->event_type, 'wishlist'),
                                        'bg-amber-100 text-amber-800' => str_contains($event->event_type, 'cart'),
                                        'bg-emerald-100 text-emerald-800' => str_contains($event->event_type, 'checkout') || str_contains($event->event_type, 'order'),
                                        'bg-zinc-100 text-zinc-700' => ! str_contains($event->event_type, 'product') && ! str_contains($event->event_type, 'cart') && ! str_contains($event->event_type, 'order'),
                                    ])>
                                        {{ $event->event_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-mono text-[11px]">
                                    @if($event->user_id)
                                        <span class="font-bold text-zinc-900">User #{{ $event->user_id }}</span>
                                    @else
                                        <span class="text-zinc-400">Guest ({{ substr($event->session_id ?? 'none', 0, 8) }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-mono text-[11px]">
                                    @if($event->entity_type)
                                        <span class="text-zinc-800">{{ $event->entity_type }}:{{ $event->entity_id ?? 'N/A' }}</span>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-[11px] text-zinc-700 break-all">
                                    {{ json_encode($event->metadata ?? []) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-zinc-400">
                                    No AI events recorded yet. Visit storefront pages or add items to cart to trigger real events!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin.layout>
