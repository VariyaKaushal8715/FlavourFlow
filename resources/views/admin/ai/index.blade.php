<x-admin.layout title="AI Engine Diagnostics">
    <div class="space-y-6">
        <!-- Page Title & Overall Readiness Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-bold uppercase tracking-wider text-amber-800">
                        Step 7 - Multilingual Intent & Language Understanding Verification
                    </span>
                </div>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">
                    AI Engine Health & Multilingual NLU Layer
                </h1>
                <p class="mt-1 text-sm text-zinc-500">
                    Automated, non-fake verification of AI Core, Tracking, Context, Reasoning, Recommendations, Provider Integration, and Multilingual Intent Understanding.
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
                    <p class="font-bold">Development & Diagnostic Isolation Notice (Step 7 - Multilingual NLU Layer)</p>
                    <p class="mt-0.5 text-amber-800">
                        This AI Engine status page verifies automatic language detection, intent parsing, entity extraction, and multilingual response generation across Gujarati, GujEnglish, Hindi, Hinglish, and English.
                    </p>
                </div>
            </div>
        </div>

        <!-- Verification Diagnostic Checks Grid -->
        <div>
            <h2 class="text-base font-bold text-zinc-950 mb-3">System Verification Checks (Steps 1–7)</h2>
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
                                <span>{{ $check['passed'] ? '✓ Verified' : '✕ Not Configured / Check' }}</span>
                            </span>
                        </div>

                        <div class="mt-4 rounded-xl border border-zinc-100 bg-zinc-50 p-3 text-[11px] font-mono text-zinc-700 break-all">
                            {{ $check['details'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Step 7 Showcase: Multilingual NLU Test Suite Matrix -->
        @if(! empty($sampleMultilingualTestResults))
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-4">
                    <div>
                        <span class="rounded bg-teal-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-teal-800">
                            Step 7 Multilingual Showcase
                        </span>
                        <h2 class="mt-1 text-base font-bold text-zinc-950">Multilingual Query Understanding & Transliteration Matrix</h2>
                        <p class="mt-0.5 text-xs text-zinc-500">Live NLU results for Gujarati, GujEnglish, Hindi, Hinglish, and English inputs.</p>
                    </div>
                    <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-bold text-teal-800 border border-teal-200">
                        5 Languages Supported
                    </span>
                </div>

                <div class="grid gap-4 sm:grid-cols-1 lg:grid-cols-2">
                    @foreach($sampleMultilingualTestResults as $item)
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 flex flex-col justify-between space-y-3">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span @class([
                                        'inline-flex rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider',
                                        'bg-purple-100 text-purple-800' => $item['detected_lang'] === 'gu',
                                        'bg-blue-100 text-blue-800' => $item['detected_lang'] === 'gujenglish',
                                        'bg-orange-100 text-orange-800' => $item['detected_lang'] === 'hi',
                                        'bg-amber-100 text-amber-800' => $item['detected_lang'] === 'hinglish',
                                        'bg-emerald-100 text-emerald-800' => $item['detected_lang'] === 'en',
                                    ])>
                                        {{ $item['label'] }} ({{ $item['detected_lang'] }})
                                    </span>
                                    <span class="text-[10px] font-mono font-bold text-zinc-600 bg-white px-2 py-0.5 rounded border border-zinc-200">
                                        {{ round($item['confidence'] * 100) }}% Confidence
                                    </span>
                                </div>

                                <p class="text-xs font-bold text-zinc-950 bg-white p-2.5 rounded-lg border border-zinc-200/80">
                                    "{{ $item['query'] }}"
                                </p>

                                <div class="mt-2.5 grid grid-cols-3 gap-2 text-[11px] font-mono">
                                    <div class="bg-white p-2 rounded border border-zinc-200">
                                        <span class="text-[9px] uppercase text-zinc-400 block font-bold">Intent:</span>
                                        <span class="font-bold text-zinc-900 truncate block">{{ $item['intent'] }}</span>
                                    </div>
                                    <div class="bg-white p-2 rounded border border-zinc-200">
                                        <span class="text-[9px] uppercase text-zinc-400 block font-bold">Entity:</span>
                                        <span class="font-bold text-purple-800 truncate block">{{ $item['product_entity'] }}</span>
                                    </div>
                                    <div class="bg-white p-2 rounded border border-zinc-200">
                                        <span class="text-[9px] uppercase text-zinc-400 block font-bold">Max Budget:</span>
                                        <span class="font-bold text-emerald-800 truncate block">{{ $item['budget_entity'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-lg border border-teal-100 bg-teal-50/70 p-2.5 text-[11px] text-teal-950 leading-snug">
                                <span class="font-bold text-teal-900 block text-[9px] uppercase tracking-wider mb-0.5">Formatted Response Output:</span>
                                {{ $item['formatted_response'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Step 6 Showcase: LLM Provider System Status -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-100 pb-4">
                <div>
                    <span class="rounded bg-sky-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-sky-800">
                        Step 6 Provider System Architecture
                    </span>
                    <h2 class="mt-1 text-base font-bold text-zinc-950">Multi-Provider System Drivers & Status</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">Configurable drivers for OpenRouter, Groq, Gemini, OpenAI, Ollama, and NullProvider.</p>
                </div>
                <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-800 border border-sky-200">
                    Active Driver: {{ config('ai.default_provider', 'openrouter') }}
                </span>
            </div>

            <!-- Provider Connection Status Banner -->
            <div id="provider-test-status" @class([
                'rounded-xl border p-4 text-xs font-mono transition',
                'bg-emerald-50 border-emerald-200 text-emerald-900' => $providerConnectionTest['success'] ?? false,
                'bg-zinc-50 border-zinc-200 text-zinc-700' => ! ($providerConnectionTest['success'] ?? false),
            ])>
                <div class="flex items-center justify-between">
                    <span id="provider-test-title" class="font-bold uppercase tracking-wider">Live Active Connection Test ({{ config('ai.default_provider', 'openrouter') }}):</span>
                    <span id="provider-test-badge" class="font-bold">{{ ($providerConnectionTest['success'] ?? false) ? 'Connected' : 'Deterministic Fallback Active' }}</span>
                </div>
                <p id="provider-test-message" class="mt-1.5 leading-relaxed">{{ $providerConnectionTest['message'] ?? 'No connection test available.' }}</p>
            </div>

            <!-- Supported Drivers Matrix -->
            <div class="grid gap-3 grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
                @foreach($providerStatusMap as $driverKey => $driverInfo)
                    <div @class([
                        'rounded-xl border p-3 flex flex-col justify-between',
                        'bg-emerald-50/40 border-emerald-200' => $driverInfo['available'],
                        'bg-zinc-50 border-zinc-200' => ! $driverInfo['available'],
                    ])>
                        <div>
                            <div class="flex items-center justify-between gap-1 mb-1">
                                <span class="text-xs font-bold text-zinc-900 uppercase font-mono">{{ $driverKey }}</span>
                                <span @class([
                                    'inline-block h-2 w-2 rounded-full',
                                    'bg-emerald-500' => $driverInfo['available'],
                                    'bg-zinc-300' => ! $driverInfo['available'],
                                ])></span>
                            </div>
                            <p class="text-[10px] font-mono text-zinc-500 truncate" title="{{ $driverInfo['model'] }}">
                                {{ $driverInfo['model'] }}
                            </p>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-[10px]">
                            @if($driverInfo['available'])
                                <span class="font-bold text-emerald-700">Available</span>
                            @else
                                <span class="font-bold text-zinc-400">Not Configured</span>
                            @endif
                            <button type="button" onclick="runProviderTest('{{ $driverKey }}')" class="font-semibold text-brand-primary hover:underline">
                                Ping
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Step 9 Showcase: Complete E-Commerce Domain Knowledge Base & BI Consultant -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-100 pb-5">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-indigo-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-indigo-800">
                            Domain Knowledge &amp; BI Consultant Copilot
                        </span>
                        <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-800">
                            18 Master Domains Loaded
                        </span>
                    </div>
                    <h2 class="mt-2 text-xl font-bold tracking-tight text-zinc-950 sm:text-2xl">
                        E-Commerce Domain Intelligence &amp; Implementation Copilot
                    </h2>
                    <p class="mt-1 text-xs text-zinc-500 max-w-3xl">
                        Multi-layer knowledge engine across 18 domains (Fundamentals, Strategy, UI/UX, Cart &amp; Checkout, Order Management, Logistics, Marketing, Retention, Analytics, Legal/GST, Tech Architecture, Security, Data, Omnichannel, International, Customer Support, Catalog, and Continuous Optimization).
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        id="seed-curriculum-btn"
                        onclick="runSeedCurriculum()"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 transition"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        <span>Re-Sync Knowledge Base</span>
                    </button>
                </div>
            </div>

            <!-- Knowledge Statistics 5-Col Metric Strip -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-700">Master Domains</p>
                    <p class="mt-1 text-2xl font-black text-indigo-950">{{ $knowledgeStats['total_domains'] ?? 0 }}</p>
                    <p class="mt-0.5 text-[11px] text-indigo-700/80">18/18 Curated</p>
                </div>
                <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Curriculum Topics</p>
                    <p class="mt-1 text-2xl font-black text-blue-950">{{ $knowledgeStats['total_topics'] ?? 0 }}</p>
                    <p class="mt-0.5 text-[11px] text-blue-700/80">Beginner &bull; Inter &bull; Adv</p>
                </div>
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Verified Items</p>
                    <p class="mt-1 text-2xl font-black text-emerald-950">{{ $knowledgeStats['total_items'] ?? 0 }}</p>
                    <p class="mt-0.5 text-[11px] text-emerald-700/80">With Code &amp; Workflows</p>
                </div>
                <div class="rounded-xl border border-violet-100 bg-violet-50/50 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-violet-700">Authoritative Sources</p>
                    <p class="mt-1 text-2xl font-black text-violet-950">{{ $knowledgeStats['total_sources'] ?? 0 }}</p>
                    <p class="mt-0.5 text-[11px] text-violet-700/80">Official Citations</p>
                </div>
                <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Identified Gaps</p>
                    <p class="mt-1 text-2xl font-black text-amber-950">{{ $knowledgeStats['pending_gaps'] ?? 0 }}</p>
                    <p class="mt-0.5 text-[11px] text-amber-700/80">Self-Detected Missing Areas</p>
                </div>
            </div>

            <!-- Consultant Simulator + Store Diagnostics Grid -->
            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Left: Interactive Consultant & Copilot Simulator (7 cols) -->
                <div class="lg:col-span-7 rounded-2xl border border-zinc-200 bg-zinc-50/60 p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-zinc-200/80 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-950 flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Domain Consultant &amp; Copilot Simulator
                            </h3>
                            <p class="text-[11px] text-zinc-500">Ask business intelligence, architecture, or code implementation questions.</p>
                        </div>
                        <span class="rounded bg-zinc-200/80 px-2 py-0.5 text-[10px] font-mono text-zinc-700">RAG + Multi-Role</span>
                    </div>

                    <!-- Role & Language Selectors -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-600 mb-1">Consultant Persona</label>
                            <select id="consultant-role" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-medium text-zinc-800 shadow-2xs focus:border-indigo-600 focus:outline-none">
                                <option value="business_intelligence">Business Intelligence &amp; Unit Economics Advisor</option>
                                <option value="technical_consultant">Technical &amp; Architecture Consultant</option>
                                <option value="implementation_copilot">Implementation Copilot (Laravel, DB, Frontend)</option>
                                <option value="architecture_advisor">System &amp; Database Architect</option>
                                <option value="operations_lead">Operations &amp; Compliance Lead</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-600 mb-1">Language</label>
                            <select id="consultant-lang" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-medium text-zinc-800 shadow-2xs focus:border-indigo-600 focus:outline-none">
                                <option value="en">English</option>
                                <option value="gu">Gujarati (ગુજરાતી)</option>
                                <option value="hi">Hindi (हिंदी)</option>
                                <option value="hinglish">Hinglish</option>
                                <option value="gujenglish">GujEnglish (Gujlish)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Quick Prompt Chips -->
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Sample Domain Inquiries:</p>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" onclick="setConsultantPrompt('What is the recommended gross margin and LTV:CAC target for D2C spice commerce?', 'business_intelligence', 'en')" class="rounded-lg border border-zinc-200 bg-white px-2 py-1 text-[11px] text-zinc-700 hover:border-indigo-400 hover:text-indigo-600 transition shadow-2xs">Gross Margin &amp; LTV:CAC</button>
                            <button type="button" onclick="setConsultantPrompt('How do database transactions and lockForUpdate prevent overselling during checkout?', 'technical_consultant', 'en')" class="rounded-lg border border-zinc-200 bg-white px-2 py-1 text-[11px] text-zinc-700 hover:border-indigo-400 hover:text-indigo-600 transition shadow-2xs">DB Concurrency &amp; Locks</button>
                            <button type="button" onclick="setConsultantPrompt('ઓર્ડર કન્ફર્મ કરવા માટે UPI પેમેન્ટ વેરિફિકેશન અને ડાયનેમિક QR કોડ કેવી રીતે કામ કરે છે?', 'technical_consultant', 'gu')" class="rounded-lg border border-zinc-200 bg-white px-2 py-1 text-[11px] text-zinc-700 hover:border-indigo-400 hover:text-indigo-600 transition shadow-2xs">UPI QR વેરિફિકેશન (ગુજરાતી)</button>
                            <button type="button" onclick="setConsultantPrompt('What are the key technical strategies to reduce shopping cart abandonment?', 'implementation_copilot', 'en')" class="rounded-lg border border-zinc-200 bg-white px-2 py-1 text-[11px] text-zinc-700 hover:border-indigo-400 hover:text-indigo-600 transition shadow-2xs">Cart Abandonment Fixes</button>
                        </div>
                    </div>

                    <!-- Query Box -->
                    <div>
                        <textarea id="consultant-query" rows="3" placeholder="Enter any e-commerce question (e.g. inventory valuation, payment security, SEO architecture, shipping logistics)..." class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-xs text-zinc-900 shadow-2xs focus:border-indigo-600 focus:outline-none font-sans"></textarea>
                    </div>

                    <div class="flex items-center justify-between">
                        <span id="consultant-status" class="text-[11px] text-zinc-500 font-medium">Ready for inquiry</span>
                        <button type="button" id="consult-submit-btn" onclick="submitConsultantQuery()" class="inline-flex items-center gap-1.5 rounded-xl bg-zinc-950 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-brand-primary transition">
                            <span>Consult AI Copilot</span>
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </button>
                    </div>

                    <!-- Simulator Result Box -->
                    <div id="consultant-result-box" class="hidden rounded-xl border border-zinc-200 bg-white p-4 space-y-3 shadow-xs">
                        <div class="flex items-center justify-between border-b border-zinc-100 pb-2">
                            <span id="consultant-result-title" class="text-xs font-bold text-zinc-900 uppercase">Consultant Response</span>
                            <span id="consultant-confidence-badge" class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">Confidence: 95%</span>
                        </div>
                        <div id="consultant-answer-text" class="text-xs leading-relaxed text-zinc-800 whitespace-pre-line font-sans"></div>

                        <!-- Retrieved Citations Strip -->
                        <div id="consultant-sources-container" class="border-t border-zinc-100 pt-2 hidden">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 mb-1">Authoritative Citations &amp; Evidence:</p>
                            <div id="consultant-sources-list" class="space-y-1 text-[11px]"></div>
                        </div>

                        <!-- Recommendations Strip -->
                        <div id="consultant-recs-container" class="border-t border-zinc-100 pt-2 hidden">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 mb-1">Actionable Recommendations:</p>
                            <ul id="consultant-recs-list" class="list-disc list-inside space-y-0.5 text-[11px] text-zinc-700"></ul>
                        </div>
                    </div>
                </div>

                <!-- Right: Live Store BI Diagnostics (5 cols) -->
                <div class="lg:col-span-5 rounded-2xl border border-zinc-200 bg-zinc-50/60 p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-zinc-200/80 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-950">Store Business Intelligence</h3>
                            <p class="text-[11px] text-zinc-500">Real database health metrics &amp; unit economics.</p>
                        </div>
                        <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">Live DB</span>
                    </div>

                    @php
                        $sales = $storeBiDiagnostics['sales_analytics'] ?? [];
                        $inventory = $storeBiDiagnostics['inventory_health'] ?? [];
                        $funnel = $storeBiDiagnostics['cart_and_conversion'] ?? [];
                    @endphp

                    <!-- Live KPI Grid -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="rounded-xl border border-zinc-200 bg-white p-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Total Sales</span>
                            <p class="mt-1 text-base font-black text-zinc-900">₹{{ number_format((float) ($sales['total_revenue'] ?? 0), 2) }}</p>
                            <span class="text-[10px] text-zinc-500">{{ $sales['total_orders'] ?? 0 }} orders</span>
                        </div>
                        <div class="rounded-xl border border-zinc-200 bg-white p-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Average Order (AOV)</span>
                            <p class="mt-1 text-base font-black text-zinc-900">₹{{ number_format((float) ($sales['average_order_value'] ?? 0), 2) }}</p>
                            <span class="text-[10px] text-emerald-600">Calculated Authoritative</span>
                        </div>
                        <div class="rounded-xl border border-zinc-200 bg-white p-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Active Products</span>
                            <p class="mt-1 text-base font-black text-zinc-900">{{ $inventory['total_active_products'] ?? 0 }}</p>
                            <span class="text-[10px] text-amber-600">{{ $inventory['low_stock_count'] ?? 0 }} low stock</span>
                        </div>
                        <div class="rounded-xl border border-zinc-200 bg-white p-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Cart Abandonment</span>
                            <p class="mt-1 text-base font-black text-zinc-900">{{ $funnel['abandonment_rate_pct'] ?? 0 }}%</p>
                            <span class="text-[10px] text-zinc-500">{{ $funnel['orders_placed_events'] ?? 0 }} / {{ $funnel['checkout_started_events'] ?? 0 }} checkout</span>
                        </div>
                    </div>

                    <!-- AI Strategic Insights & Strengths -->
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-3.5 space-y-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-800">AI Strengths &amp; Opportunities:</p>
                        <ul class="space-y-1 text-[11px] text-emerald-900">
                            @forelse(($storeBiDiagnostics['strengths'] ?? []) as $str)
                                <li class="flex items-start gap-1.5">
                                    <span class="text-emerald-600 font-bold">✓</span>
                                    <span>{{ $str }}</span>
                                </li>
                            @empty
                                <li>Store telemetry active and healthy.</li>
                            @endforelse
                        </ul>
                    </div>

                    <!-- AI Risk Signals -->
                    @if(! empty($storeBiDiagnostics['risks_identified']))
                        <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-3.5 space-y-2">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-amber-800">Identified Risk Signals:</p>
                            <ul class="space-y-1 text-[11px] text-amber-900">
                                @foreach($storeBiDiagnostics['risks_identified'] as $risk)
                                    <li class="flex items-start gap-1.5">
                                        <span class="text-amber-600 font-bold">⚠</span>
                                        <span>{{ $risk }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <!-- E-Commerce 18 Master Domains Curriculum Explorer -->
            <div class="border-t border-zinc-100 pt-5 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-950">18 Master E-Commerce Knowledge Domains</h3>
                        <p class="text-[11px] text-zinc-500">Comprehensive curriculum mapped across Beginner, Intermediate, and Advanced tiers with separated memory layers.</p>
                    </div>
                    <span class="text-xs font-semibold text-zinc-600">Showing 18 Domains</span>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($knowledgeDomains as $domain)
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-3.5 flex flex-col justify-between hover:border-zinc-300 transition">
                            <div>
                                <div class="flex items-start justify-between gap-2 mb-1.5">
                                    <span class="rounded bg-zinc-200 px-1.5 py-0.5 font-mono text-[10px] font-bold text-zinc-700">
                                        {{ $domain->code }}
                                    </span>
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[9px] font-bold text-emerald-800">
                                        {{ $domain->topics_count ?? $domain->topics->count() }} Topics
                                    </span>
                                </div>
                                <h4 class="text-xs font-bold text-zinc-900">{{ $domain->name }}</h4>
                                <p class="mt-1 text-[11px] text-zinc-500 line-clamp-2 leading-relaxed">{{ $domain->description }}</p>
                            </div>

                            <div class="mt-3 border-t border-zinc-200/70 pt-2 space-y-1">
                                @foreach($domain->topics->take(3) as $topic)
                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="text-zinc-700 truncate max-w-[180px]">{{ $topic->name }}</span>
                                        <span @class([
                                            'rounded px-1 text-[9px] font-bold uppercase',
                                            'bg-sky-100 text-sky-800' => $topic->level === 'beginner',
                                            'bg-amber-100 text-amber-800' => $topic->level === 'intermediate',
                                            'bg-purple-100 text-purple-800' => $topic->level === 'advanced',
                                        ])>
                                            {{ $topic->level }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Knowledge Gaps Tracker -->
            @if($knowledgeGaps->isNotEmpty())
                <div class="border-t border-zinc-100 pt-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-950 flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                Self-Detected Knowledge Gaps
                            </h3>
                            <p class="text-[11px] text-zinc-500">Unresolved customer or system queries logged for curriculum expansion.</p>
                        </div>
                        <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800">
                            {{ $knowledgeGaps->count() }} Gaps
                        </span>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-zinc-50 text-[10px] font-bold uppercase text-zinc-400">
                                <tr>
                                    <th class="px-4 py-2.5">Domain</th>
                                    <th class="px-4 py-2.5">Query / Topic</th>
                                    <th class="px-4 py-2.5">Description</th>
                                    <th class="px-4 py-2.5">Status</th>
                                    <th class="px-4 py-2.5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 font-mono text-[11px]">
                                @foreach($knowledgeGaps as $gap)
                                    <tr id="gap-row-{{ $gap->id }}">
                                        <td class="px-4 py-2.5 font-bold text-zinc-900 font-sans">
                                            {{ $gap->domain?->name ?? 'Unmapped' }}
                                        </td>
                                        <td class="px-4 py-2.5 text-zinc-700 font-sans">
                                            {{ $gap->query_or_topic }}
                                        </td>
                                        <td class="px-4 py-2.5 text-zinc-500 font-sans">
                                            {{ $gap->description }}
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <span @class([
                                                'rounded px-1.5 py-0.5 text-[9px] font-bold uppercase',
                                                'bg-amber-100 text-amber-800' => $gap->status === 'identified',
                                                'bg-blue-100 text-blue-800' => $gap->status === 'learning_in_progress',
                                                'bg-emerald-100 text-emerald-800' => $gap->status === 'resolved',
                                            ])>
                                                {{ $gap->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5 text-right">
                                            @if($gap->status !== 'resolved')
                                                <button type="button" onclick="resolveGap({{ $gap->id }})" class="rounded-lg bg-zinc-950 px-2.5 py-1 text-[10px] font-bold text-white hover:bg-emerald-600 transition">
                                                    Resolve
                                                </button>
                                            @else
                                                <span class="text-[10px] text-zinc-400">Resolved</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

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

    <script>
        function runProviderTest(provider) {
            const statusBox = document.getElementById('provider-test-status');
            const title = document.getElementById('provider-test-title');
            const badge = document.getElementById('provider-test-badge');
            const message = document.getElementById('provider-test-message');

            if (message) {
                message.innerText = 'Pinging provider [' + provider + '] endpoint...';
            }
            if (badge) {
                badge.innerText = 'Testing...';
            }

            fetch('{{ route("admin.ai.providerTest") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ provider: provider })
            })
            .then(res => res.json())
            .then(data => {
                if (title) title.innerText = 'Provider Connectivity Test: ' + data.provider.toUpperCase();
                if (badge) badge.innerText = data.success ? 'Connected' : 'Failed / Inactive';
                if (message) message.innerText = data.message;

                if (statusBox) {
                    if (data.success) {
                        statusBox.className = 'rounded-xl border p-4 text-xs font-mono transition bg-emerald-50 border-emerald-200 text-emerald-900';
                    } else {
                        statusBox.className = 'rounded-xl border p-4 text-xs font-mono transition bg-rose-50 border-rose-200 text-rose-900';
                    }
                }
            })
            .catch(err => {
                if (title) title.innerText = 'Provider Test Error: ' + provider;
                if (badge) badge.innerText = 'Error';
                if (message) message.innerText = 'Network error during provider test: ' + err.message;
                if (statusBox) {
                    statusBox.className = 'rounded-xl border p-4 text-xs font-mono transition bg-rose-50 border-rose-200 text-rose-900';
                }
            });
        }

        function runSeedCurriculum() {
            const btn = document.getElementById('seed-curriculum-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span>Syncing Knowledge...</span>';
            }

            fetch('{{ route("admin.ai.knowledge.seed") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message || 'Curriculum synced successfully!');
                window.location.reload();
            })
            .catch(err => {
                alert('Error syncing curriculum: ' + err.message);
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<span>Re-Sync Knowledge Base</span>';
                }
            });
        }

        function setConsultantPrompt(prompt, role, lang) {
            const queryEl = document.getElementById('consultant-query');
            const roleEl = document.getElementById('consultant-role');
            const langEl = document.getElementById('consultant-lang');

            if (queryEl) queryEl.value = prompt;
            if (roleEl && role) roleEl.value = role;
            if (langEl && lang) langEl.value = lang;
        }

        function submitConsultantQuery() {
            const queryEl = document.getElementById('consultant-query');
            const roleEl = document.getElementById('consultant-role');
            const langEl = document.getElementById('consultant-lang');
            const statusEl = document.getElementById('consultant-status');
            const submitBtn = document.getElementById('consult-submit-btn');

            const resultBox = document.getElementById('consultant-result-box');
            const resultTitle = document.getElementById('consultant-result-title');
            const confBadge = document.getElementById('consultant-confidence-badge');
            const answerText = document.getElementById('consultant-answer-text');
            const sourcesContainer = document.getElementById('consultant-sources-container');
            const sourcesList = document.getElementById('consultant-sources-list');
            const recsContainer = document.getElementById('consultant-recs-container');
            const recsList = document.getElementById('consultant-recs-list');

            const query = queryEl ? queryEl.value.trim() : '';
            if (!query) {
                alert('Please enter a question for the consultant.');
                if (queryEl) queryEl.focus();
                return;
            }

            if (statusEl) statusEl.innerText = 'Retrieving knowledge and reasoning...';
            if (submitBtn) submitBtn.disabled = true;

            fetch('{{ route("admin.ai.knowledge.consult") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    query: query,
                    role: roleEl ? roleEl.value : 'business_intelligence',
                    language: langEl ? langEl.value : 'en'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (statusEl) statusEl.innerText = 'Ready';
                if (submitBtn) submitBtn.disabled = false;

                if (!data.success || !data.data) {
                    alert(data.message || 'Could not consult AI.');
                    return;
                }

                const d = data.data;
                if (resultBox) resultBox.classList.remove('hidden');
                if (resultTitle) resultTitle.innerText = (d.role || 'Consultant').toUpperCase().replace(/_/g, ' ') + ' ADVISORY';
                if (confBadge) confBadge.innerText = 'Confidence: ' + Math.round((d.confidence || 0.9) * 100) + '%';
                if (answerText) answerText.innerText = d.answer;

                // Citations
                if (sourcesContainer && sourcesList) {
                    sourcesList.innerHTML = '';
                    if (d.sources && d.sources.length > 0) {
                        sourcesContainer.classList.remove('hidden');
                        d.sources.forEach(s => {
                            const div = document.createElement('div');
                            div.className = 'flex items-center justify-between gap-2 rounded-lg bg-zinc-50 px-2 py-1 border border-zinc-200/60';
                            div.innerHTML = '<span class="font-bold text-zinc-900 truncate">📖 ' + (s.title || 'Source') + ' (' + (s.source_type || 'standard') + ')</span><span class="rounded bg-indigo-100 px-1.5 text-[9px] font-bold text-indigo-800">Auth: ' + (s.authority_score || 95) + '</span>';
                            sourcesList.appendChild(div);
                        });
                    } else {
                        sourcesContainer.classList.add('hidden');
                    }
                }

                // Recommendations
                if (recsContainer && recsList) {
                    recsList.innerHTML = '';
                    if (d.recommendations && d.recommendations.length > 0) {
                        recsContainer.classList.remove('hidden');
                        d.recommendations.forEach(r => {
                            const li = document.createElement('li');
                            li.innerText = r;
                            recsList.appendChild(li);
                        });
                    } else {
                        recsContainer.classList.add('hidden');
                    }
                }
            })
            .catch(err => {
                if (statusEl) statusEl.innerText = 'Error';
                if (submitBtn) submitBtn.disabled = false;
                alert('Consultation failed: ' + err.message);
            });
        }

        function resolveGap(gapId) {
            if (!confirm('Mark knowledge gap #' + gapId + ' as resolved?')) return;

            fetch('{{ url("admin/ai/knowledge/gaps") }}/' + gapId + '/resolve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ notes: 'Resolved by Admin' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = document.getElementById('gap-row-' + gapId);
                    if (row) {
                        row.classList.add('opacity-40');
                    }
                    alert(data.message);
                } else {
                    alert(data.message || 'Failed to resolve gap');
                }
            })
            .catch(err => alert('Error: ' + err.message));
        }
    </script>
</x-admin.layout>
