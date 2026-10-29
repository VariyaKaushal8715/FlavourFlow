<x-admin.layout title="Payment Settings">
    <main class="mx-auto w-full max-w-5xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <div class="mb-4 flex items-center gap-2 text-xs font-semibold text-zinc-500">
            <a href="{{ route('admin.index') }}" class="inline-flex items-center gap-1 text-zinc-600 hover:text-red-700 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Dashboard
            </a>
            <span>/</span>
            <a href="{{ route('admin.payments.index') }}" class="text-zinc-600 hover:text-red-700 transition">
                Payments
            </a>
            <span>/</span>
            <span class="text-zinc-900">Payment Gateway Settings</span>
        </div>

        {{-- Page Header --}}
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-200 pb-6">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">Payment Gateway Settings</h1>
                    @if (($activeGateway ?? 'demo') === 'demo')
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-900 ring-1 ring-inset ring-amber-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-600"></span>
                            DEMO MODE ACTIVE
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-900 ring-1 ring-inset ring-emerald-500">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            {{ strtoupper($activeGateway) }} ({{ strtoupper($environment ?? 'SANDBOX') }})
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-zinc-500">Configure Demo mode, Cashfree, or Razorpay. Genuine merchant accounts and administrator password confirmation are required for live operations.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.payments.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                    <svg class="h-4 w-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                    View Transactions
                </a>
            </div>
        </div>

        {{-- Success Notification --}}
        @if (session('status'))
            <div class="mb-6 flex items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm font-medium text-emerald-900 shadow-sm" x-data="{ show: true }" x-show="show">
                <div class="flex items-center gap-3">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-white">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    </div>
                    <p>{{ session('status') }}</p>
                </div>
                <button type="button" @click="show = false" class="text-xs font-semibold text-emerald-700 hover:text-emerald-950">Dismiss</button>
            </div>
        @endif

        {{-- Validation Errors Banner --}}
        @if (isset($errors) && $errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900 shadow-sm">
                <div class="flex items-center gap-2 font-bold text-red-800">
                    <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    <span>Operation failed:</span>
                </div>
                <ul class="mt-2 list-inside list-disc text-xs text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <p class="mt-3 text-xs text-red-800 font-medium">Security Guard: Re-authenticating administrator password is required for security-critical configuration updates.</p>
            </div>
        @endif

        {{-- SECTION 1: Active Gateway & Environment Control --}}
        <div class="mb-8 rounded-2xl border-2 border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="border-b border-zinc-100 pb-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-lg font-bold text-zinc-950">Active Payment Gateway & Operating Mode</h2>
                    <span class="rounded bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-900">DEMO & PRODUCTION ISOLATION</span>
                </div>
                <p class="mt-1 text-xs text-zinc-500">
                    Switch between Zero-Credential Demo Mode, Cashfree, or Razorpay. Live payments cannot be activated without verified credentials and confirmed password.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.payments.settings.gateway.update') }}" class="mt-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid gap-6 md:grid-cols-2">
                    {{-- Active Gateway Selector --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700">
                            Active Gateway Provider <span class="text-red-500">*</span>
                        </label>
                        <p class="mt-0.5 text-[11px] text-zinc-500">Controls which provider is selected when customers choose Online Payment.</p>
                        <div class="mt-3 space-y-2">
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition hover:bg-amber-50/50 {{ ($activeGateway ?? 'demo') === 'demo' ? 'border-amber-400 bg-amber-50/40 ring-1 ring-amber-400' : 'border-zinc-200' }}">
                                <input type="radio" name="active_gateway" value="demo" class="mt-1 h-4 w-4 text-brand-primary" @checked(($activeGateway ?? 'demo') === 'demo')>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-zinc-900">Demo Mode</span>
                                        <span class="rounded bg-amber-200 px-2 py-0.2 text-[9px] font-extrabold uppercase text-amber-950">ZERO CREDENTIALS</span>
                                    </div>
                                    <p class="text-[11px] text-zinc-500">Zero external accounts or banking API keys needed. Safe evaluation.</p>
                                </div>
                            </label>

                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition hover:bg-emerald-50/50 {{ ($activeGateway ?? '') === 'cashfree' ? 'border-emerald-400 bg-emerald-50/40 ring-1 ring-emerald-400' : 'border-zinc-200' }}">
                                <input type="radio" name="active_gateway" value="cashfree" class="mt-1 h-4 w-4 text-brand-primary" @checked(($activeGateway ?? '') === 'cashfree')>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-zinc-900">Cashfree Payments</span>
                                        <span class="rounded bg-zinc-100 px-1.5 py-0.2 text-[9px] font-bold text-zinc-700">{{ $isCfConfigured ? 'CREDENTIALS SET' : 'UNCONFIGURED' }}</span>
                                    </div>
                                    <p class="text-[11px] text-zinc-500">Standard checkout session & webhook verification.</p>
                                </div>
                            </label>

                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition hover:bg-red-50/50 {{ ($activeGateway ?? '') === 'razorpay' ? 'border-red-400 bg-red-50/40 ring-1 ring-red-400' : 'border-zinc-200' }}">
                                <input type="radio" name="active_gateway" value="razorpay" class="mt-1 h-4 w-4 text-brand-primary" @checked(($activeGateway ?? '') === 'razorpay')>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-zinc-900">Razorpay Gateway</span>
                                        <span class="rounded bg-zinc-100 px-1.5 py-0.2 text-[9px] font-bold text-zinc-700">{{ $isConfigured ? 'CREDENTIALS SET' : 'UNCONFIGURED' }}</span>
                                    </div>
                                    <p class="text-[11px] text-zinc-500">Standard Razorpay Checkout with server-side HMAC signature verification.</p>
                                </div>
                            </label>
                        </div>
                        @error('active_gateway')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Environment Selector --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700">
                            Operating Environment <span class="text-red-500">*</span>
                        </label>
                        <p class="mt-0.5 text-[11px] text-zinc-500">Sandbox uses test API endpoints; Live processes real INR transactions.</p>
                        <div class="mt-3 space-y-2">
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition hover:bg-zinc-50 {{ ($environment ?? 'sandbox') === 'sandbox' ? 'border-zinc-800 bg-zinc-50/70 ring-1 ring-zinc-800' : 'border-zinc-200' }}">
                                <input type="radio" name="environment" value="sandbox" class="mt-1 h-4 w-4 text-brand-primary" @checked(($environment ?? 'sandbox') === 'sandbox')>
                                <div>
                                    <span class="text-xs font-bold text-zinc-900">Sandbox (Test Mode)</span>
                                    <p class="text-[11px] text-zinc-500">Safe sandbox endpoints. No real currency is charged.</p>
                                </div>
                            </label>

                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition hover:bg-rose-50 {{ ($environment ?? '') === 'live' ? 'border-rose-500 bg-rose-50/60 ring-1 ring-rose-500' : 'border-zinc-200' }}">
                                <input type="radio" name="environment" value="live" class="mt-1 h-4 w-4 text-brand-primary" @checked(($environment ?? '') === 'live')>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold text-rose-950">Live Production Mode</span>
                                        <span class="rounded bg-rose-200 px-1.5 py-0.2 text-[9px] font-extrabold uppercase text-rose-900">REAL MONEY</span>
                                    </div>
                                    <p class="text-[11px] text-zinc-500">Requires verified merchant credentials and bank account KYC.</p>
                                </div>
                            </label>
                        </div>
                        @error('environment')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror

                        {{-- Production Notice --}}
                        <div class="mt-4 rounded-xl bg-amber-50 p-3 text-[11px] text-amber-950 border border-amber-200 flex items-start gap-2">
                            <svg class="h-4 w-4 text-amber-700 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <div>
                                <span class="font-bold">Important Merchant Requirement:</span>
                                Genuine live payments require your own merchant gateway account, business verification, and bank KYC. FlavourFlow never activates live payments automatically.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Password Confirmation Field for Gateway Change --}}
                <div class="border-t border-zinc-100 pt-5">
                    <div class="max-w-md">
                        <label for="current_password_gateway" class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                            Confirm Administrator Password <span class="text-red-500">*</span>
                        </label>
                        <p class="mt-0.5 text-[11px] text-zinc-500">Re-enter your admin password to authorize changing payment routing or environment.</p>
                        <input
                            type="password"
                            id="current_password_gateway"
                            name="current_password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your current admin password"
                            class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-600/10"
                        >
                        @error('current_password')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end border-t border-zinc-100 pt-4">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-zinc-950 px-6 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-primary"
                    >
                        Save Gateway & Environment Settings
                    </button>
                </div>
            </form>
        </div>

        {{-- SECTION 2: Razorpay API Credentials --}}
        <div class="mb-8 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8" x-data="{ submittingRzp: false, showSecret: false, showWebhookSecret: false }">
            <div class="border-b border-zinc-100 pb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-zinc-950">Razorpay Payment Settings & API Credentials</h2>
                    <p class="mt-1 text-xs text-zinc-500">
                        Credentials are stored encrypted on the server. Never exposed to browser or customers.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if ($isConfigured)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            CONFIGURED ({{ strtoupper($mode) }})
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-600">
                            NOT CONFIGURED
                        </span>
                    @endif

                    @if ($isConfigured)
                        <form method="POST" action="{{ route('admin.payments.settings.test') }}" class="inline">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1 rounded-xl border border-zinc-300 bg-white px-3 py-1 text-xs font-semibold text-zinc-700 shadow-xs transition hover:bg-zinc-50"
                            >
                                <svg class="h-3.5 w-3.5 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.652a3.75 3.75 0 010-5.304m5.304 0a3.75 3.75 0 010 5.304m-7.424 2.12a6.75 6.75 0 010-9.546m9.544 0a6.75 6.75 0 010 9.546M12 12h.008v.008H12V12z" /></svg>
                                Test API Connection
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('admin.payments.settings.update') }}" class="mt-6 space-y-5" @submit="submittingRzp = true">
                @csrf
                @method('PUT')

                {{-- Key ID Field --}}
                <div>
                    <label for="key_id" class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                        Razorpay Key ID <span class="text-red-500">*</span>
                    </label>
                    <div class="relative mt-1.5">
                        <input
                            type="text"
                            id="key_id"
                            name="key_id"
                            value="{{ old('key_id', $keyId) }}"
                            placeholder="e.g. rzp_test_1234567890abcdef"
                            required
                            autocomplete="off"
                            spellcheck="false"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 font-mono text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-red-600 focus:ring-2 focus:ring-red-600/10"
                        >
                    </div>
                    @error('key_id')
                        <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Key Secret Field --}}
                <div>
                    <div class="flex items-center justify-between">
                        <label for="key_secret" class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                            Razorpay Key Secret
                            @if (! $hasConfiguredSecret)
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        @if ($hasConfiguredSecret)
                            <span class="inline-flex items-center gap-1 rounded bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                Protected & Masked (••••••••••••••••)
                            </span>
                        @endif
                    </div>
                    <div class="relative mt-1.5">
                        <input
                            :type="showSecret ? 'text' : 'password'"
                            id="key_secret"
                            name="key_secret"
                            value=""
                            autocomplete="new-password"
                            spellcheck="false"
                            placeholder="{{ $hasConfiguredSecret ? '•••••••••••••••• (Leave blank to keep existing encrypted secret)' : 'Enter Razorpay Key Secret' }}"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 pr-12 font-mono text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-red-600 focus:ring-2 focus:ring-red-600/10"
                        >
                        <button
                            type="button"
                            @click="showSecret = !showSecret"
                            class="absolute inset-y-0 right-0 flex items-center px-4 text-zinc-400 hover:text-zinc-600 transition"
                            title="Toggle secret visibility"
                        >
                            <svg x-show="!showSecret" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <svg x-show="showSecret" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                        </button>
                    </div>
                    @error('key_secret')
                        <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Webhook Secret Field --}}
                <div>
                    <div class="flex items-center justify-between">
                        <label for="webhook_secret" class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                            Razorpay Webhook Secret (Optional)
                        </label>
                        @if ($hasConfiguredWebhookSecret)
                            <span class="inline-flex items-center gap-1 rounded bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                Protected & Masked (••••••••••••••••)
                            </span>
                        @endif
                    </div>
                    <div class="relative mt-1.5">
                        <input
                            :type="showWebhookSecret ? 'text' : 'password'"
                            id="webhook_secret"
                            name="webhook_secret"
                            value=""
                            autocomplete="new-password"
                            spellcheck="false"
                            placeholder="{{ $hasConfiguredWebhookSecret ? '•••••••••••••••• (Leave blank to keep existing encrypted secret)' : 'Enter Razorpay Webhook Secret' }}"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 pr-12 font-mono text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-red-600 focus:ring-2 focus:ring-red-600/10"
                        >
                        <button
                            type="button"
                            @click="showWebhookSecret = !showWebhookSecret"
                            class="absolute inset-y-0 right-0 flex items-center px-4 text-zinc-400 hover:text-zinc-600 transition"
                            title="Toggle webhook secret visibility"
                        >
                            <svg x-show="!showWebhookSecret" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <svg x-show="showWebhookSecret" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                        </button>
                    </div>
                    @error('webhook_secret')
                        <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Razorpay Webhook Endpoint Display --}}
                <div class="rounded-xl border border-zinc-200 bg-zinc-50/80 p-3.5">
                    <p class="text-xs font-bold text-zinc-900">Razorpay Webhook Endpoint URL</p>
                    <p class="mt-0.5 text-[11px] text-zinc-500">Configure this endpoint in your Razorpay Dashboard (Settings &gt; Webhooks) with events: <code>payment.captured</code>, <code>order.paid</code>, <code>payment.failed</code>, and <code>refund.processed</code>.</p>
                    <div class="mt-2 flex items-center gap-2">
                        <input type="text" readonly value="{{ $razorpayWebhookUrl }}" class="flex-1 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 font-mono text-xs text-zinc-800 outline-none select-all">
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $razorpayWebhookUrl }}'); alert('Webhook URL copied to clipboard!');" class="rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700 shadow-xs hover:bg-zinc-100">
                            Copy
                        </button>
                    </div>
                </div>

                {{-- Admin Password Confirmation --}}
                <div class="border-t border-zinc-100 pt-4">
                    <label for="current_password_rzp" class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                        Admin Current Password <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="password"
                        id="current_password_rzp"
                        name="current_password"
                        required
                        autocomplete="current-password"
                        placeholder="Re-enter your password to save credentials"
                        class="mt-1.5 max-w-md w-full rounded-xl border border-zinc-300 bg-white px-4 py-2 text-sm text-zinc-900 outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-600/10"
                    >
                    @error('current_password')
                        <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end border-t border-zinc-100 pt-4">
                    <button
                        type="submit"
                        :disabled="submittingRzp"
                        class="inline-flex items-center justify-center rounded-xl bg-zinc-950 px-6 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-primary disabled:opacity-50"
                    >
                        <span x-show="!submittingRzp">Save Razorpay Credentials</span>
                        <span x-show="submittingRzp" x-cloak>Saving...</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- SECTION 3: Cashfree API Credentials --}}
        <div class="mb-8 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8" x-data="{ submittingCf: false, showCfSecret: false }">
            <div class="border-b border-zinc-100 pb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-zinc-950">Cashfree Payments Credentials (Future Production)</h2>
                    <p class="mt-1 text-xs text-zinc-500">
                        Credentials are stored encrypted on the server. Never exposed to browser or customers.
                    </p>
                </div>
                <div>
                    @if ($isCfConfigured)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            CONFIGURED
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-600">
                            NOT CONFIGURED
                        </span>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('admin.payments.settings.cashfree.update') }}" class="mt-6 space-y-5" @submit="submittingCf = true">
                @csrf
                @method('PUT')

                {{-- Cashfree App ID Field --}}
                <div>
                    <label for="cashfree_app_id" class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                        Cashfree App ID <span class="text-red-500">*</span>
                    </label>
                    <div class="relative mt-1.5">
                        <input
                            type="text"
                            id="cashfree_app_id"
                            name="cashfree_app_id"
                            value="{{ old('cashfree_app_id', $cfAppId) }}"
                            placeholder="e.g. CF1234567890abcdef"
                            required
                            autocomplete="off"
                            spellcheck="false"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 font-mono text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10"
                        >
                    </div>
                    @error('cashfree_app_id')
                        <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Cashfree Secret Key Field --}}
                <div>
                    <div class="flex items-center justify-between">
                        <label for="cashfree_secret_key" class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                            Cashfree Secret Key
                            @if (! $hasConfiguredCfSecret)
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        @if ($hasConfiguredCfSecret)
                            <span class="inline-flex items-center gap-1 rounded bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                Protected & Masked (••••••••••••••••)
                            </span>
                        @endif
                    </div>
                    <div class="relative mt-1.5">
                        <input
                            :type="showCfSecret ? 'text' : 'password'"
                            id="cashfree_secret_key"
                            name="cashfree_secret_key"
                            value=""
                            autocomplete="new-password"
                            spellcheck="false"
                            placeholder="{{ $hasConfiguredCfSecret ? '•••••••••••••••• (Leave blank to keep existing encrypted secret)' : 'Enter Cashfree Secret Key' }}"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 pr-12 font-mono text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10"
                        >
                        <button
                            type="button"
                            @click="showCfSecret = !showCfSecret"
                            class="absolute inset-y-0 right-0 flex items-center px-4 text-zinc-400 hover:text-zinc-600 transition"
                            title="Toggle secret visibility"
                        >
                            <svg x-show="!showCfSecret" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <svg x-show="showCfSecret" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                        </button>
                    </div>
                    @error('cashfree_secret_key')
                        <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Cashfree Webhook Endpoint Display --}}
                <div class="rounded-xl border border-zinc-200 bg-zinc-50/80 p-3.5">
                    <p class="text-xs font-bold text-zinc-900">Cashfree Webhook Endpoint URL</p>
                    <p class="mt-0.5 text-[11px] text-zinc-500">Configure this endpoint in your Cashfree Merchant Dashboard (Developers &gt; Webhooks) with event <code>PAYMENT_SUCCESS_WEBHOOK</code> and refund events.</p>
                    <div class="mt-2 flex items-center gap-2">
                        <input type="text" readonly value="{{ $cashfreeWebhookUrl }}" class="flex-1 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 font-mono text-xs text-zinc-800 outline-none select-all">
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $cashfreeWebhookUrl }}'); alert('Webhook URL copied to clipboard!');" class="rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700 shadow-xs hover:bg-zinc-100">
                            Copy
                        </button>
                    </div>
                </div>

                {{-- Admin Password Confirmation --}}
                <div class="border-t border-zinc-100 pt-4">
                    <label for="current_password_cf" class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                        Admin Current Password <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="password"
                        id="current_password_cf"
                        name="current_password"
                        required
                        autocomplete="current-password"
                        placeholder="Re-enter your password to save credentials"
                        class="mt-1.5 max-w-md w-full rounded-xl border border-zinc-300 bg-white px-4 py-2 text-sm text-zinc-900 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10"
                    >
                    @error('current_password')
                        <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between border-t border-zinc-100 pt-4">
                    @if ($isCfConfigured)
                        <form method="POST" action="{{ route('admin.payments.settings.cashfree.test') }}" class="inline">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1 rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-xs transition hover:bg-zinc-50"
                            >
                                <svg class="h-3.5 w-3.5 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.652a3.75 3.75 0 010-5.304m5.304 0a3.75 3.75 0 010 5.304m-7.424 2.12a6.75 6.75 0 010-9.546m9.544 0a6.75 6.75 0 010 9.546M12 12h.008v.008H12V12z" /></svg>
                                Test API Connection
                            </button>
                        </form>
                    @else
                        <div></div>
                    @endif

                    <button
                        type="submit"
                        :disabled="submittingCf"
                        class="inline-flex items-center justify-center rounded-xl bg-zinc-950 px-6 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-50"
                    >
                        <span x-show="!submittingCf">Save Cashfree Credentials</span>
                        <span x-show="submittingCf" x-cloak>Saving...</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- SECTION 4: UPI QR Code Settings --}}
        <div class="mb-8 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8" x-data="{ submittingUpi: false }">
            <div class="border-b border-zinc-100 pb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-zinc-950">UPI QR Code Settings (Manual Confirmation)</h2>
                        <span class="rounded bg-indigo-100 px-2.5 py-0.5 text-[10px] font-bold text-indigo-900">QR GENERATION ONLY</span>
                    </div>
                    <p class="mt-1 text-xs text-zinc-500">
                        Configure your Merchant UPI ID (VPA) and payee business details to generate scannable UPI QR codes at checkout. Zero gateway credentials or bank logins required.
                    </p>
                </div>
                <div>
                    @if ($isUpiConfigured)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            {{ $isUpiEnabled ? 'ACTIVE & CONFIGURED' : 'CONFIGURED (DISABLED)' }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                            NOT CONFIGURED
                        </span>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('admin.payments.settings.upi.update') }}" class="mt-6 space-y-5" @submit="submittingUpi = true">
                @csrf
                @method('PUT')

                <div class="grid gap-6 md:grid-cols-2">
                    {{-- Merchant UPI ID (VPA) --}}
                    <div>
                        <label for="upi_id" class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                            Merchant UPI ID (VPA) <span class="text-red-500">*</span>
                        </label>
                        <p class="mt-0.5 text-[11px] text-zinc-500">Standard Virtual Payment Address (e.g. <code>store@okhdfcbank</code>, <code>merchant@upi</code>, <code>9876543210@paytm</code>).</p>
                        <div class="relative mt-2">
                            <input
                                type="text"
                                id="upi_id"
                                name="upi_id"
                                value="{{ old('upi_id', $upiId) }}"
                                placeholder="e.g. flavourflow@okhdfcbank"
                                required
                                autocomplete="off"
                                spellcheck="false"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 font-mono text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/10"
                            >
                        </div>
                        @error('upi_id')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Payee / Business Name --}}
                    <div>
                        <label for="upi_name" class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                            Payee / Business Name <span class="text-red-500">*</span>
                        </label>
                        <p class="mt-0.5 text-[11px] text-zinc-500">Name shown to customers in their UPI app when scanning the QR code.</p>
                        <div class="relative mt-2">
                            <input
                                type="text"
                                id="upi_name"
                                name="upi_name"
                                value="{{ old('upi_name', $upiName) }}"
                                placeholder="e.g. FlavourFlow Spices"
                                required
                                autocomplete="off"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/10"
                            >
                        </div>
                        @error('upi_name')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Transaction Note & Enable Toggle --}}
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="upi_note" class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                            Default Transaction Note / Prefix
                        </label>
                        <p class="mt-0.5 text-[11px] text-zinc-500">Optional note prefix attached to the UPI payment URI (Order reference is appended automatically).</p>
                        <div class="relative mt-2">
                            <input
                                type="text"
                                id="upi_note"
                                name="upi_note"
                                value="{{ old('upi_note', $upiNote) }}"
                                placeholder="e.g. FlavourFlow Order"
                                autocomplete="off"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/10"
                            >
                        </div>
                        @error('upi_note')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col justify-end">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-zinc-200 bg-zinc-50/50 p-3.5 transition hover:bg-zinc-50">
                            <input
                                type="checkbox"
                                name="upi_enabled"
                                value="1"
                                class="mt-1 h-4 w-4 rounded text-brand-primary focus:ring-brand-primary"
                                @checked(old('upi_enabled', $isUpiEnabled))
                            >
                            <div>
                                <span class="text-xs font-bold text-zinc-900">Enable UPI QR Payment Option at Checkout</span>
                                <p class="text-[11px] text-zinc-500">When enabled, customers can select "UPI QR Code" as their payment method on the checkout page.</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Informational Policy Box --}}
                <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-xs text-amber-950 flex items-start gap-3">
                    <svg class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <div class="space-y-1">
                        <p class="font-bold">Manual Confirmation Requirement:</p>
                        <p>Orders placed via UPI QR Code are registered in <strong>Pending</strong> status. FlavourFlow does not perform automated bank scraping or simulated verification for UPI. Once you verify payment receipt in your bank or UPI app, mark the order <strong>Confirmed</strong> in the Orders panel.</p>
                    </div>
                </div>

                {{-- Admin Password Confirmation --}}
                <div class="border-t border-zinc-100 pt-4">
                    <label for="current_password_upi" class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                        Admin Current Password <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="password"
                        id="current_password_upi"
                        name="current_password"
                        required
                        autocomplete="current-password"
                        placeholder="Re-enter your password to save UPI settings"
                        class="mt-1.5 max-w-md w-full rounded-xl border border-zinc-300 bg-white px-4 py-2 text-sm text-zinc-900 outline-none transition focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/10"
                    >
                    @error('current_password')
                        <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end border-t border-zinc-100 pt-4">
                    <button
                        type="submit"
                        :disabled="submittingUpi"
                        class="inline-flex items-center justify-center rounded-xl bg-zinc-950 px-6 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-primary disabled:opacity-50"
                    >
                        <span x-show="!submittingUpi">Save UPI QR Settings</span>
                        <span x-show="submittingUpi" x-cloak>Saving...</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- SECTION 5: Security Audit Log --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-100 pb-5">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-zinc-950">Payment Configuration Security Audit Log</h2>
                        <span class="rounded bg-zinc-100 px-2 py-0.5 text-[10px] font-bold text-zinc-700">TAMPER-EVIDENT</span>
                    </div>
                    <p class="mt-1 text-xs text-zinc-500">
                        All configuration modifications and password verifications are logged. Secrets are strictly redacted.
                    </p>
                </div>
                <div class="text-xs text-zinc-400">
                    Latest {{ count($auditLogs ?? []) }} events
                </div>
            </div>

            @if(count($auditLogs ?? []) > 0)
                <div class="mt-6 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-zinc-200 bg-zinc-50 text-[11px] font-bold uppercase tracking-wider text-zinc-600">
                            <tr>
                                <th class="px-4 py-3">Timestamp</th>
                                <th class="px-4 py-3">Administrator</th>
                                <th class="px-4 py-3">Action</th>
                                <th class="px-4 py-3">Gateway</th>
                                <th class="px-4 py-3">Environment</th>
                                <th class="px-4 py-3">IP Address</th>
                                <th class="px-4 py-3">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 text-zinc-700 font-mono">
                            @foreach($auditLogs as $log)
                                <tr class="hover:bg-zinc-50/70 transition">
                                    <td class="px-4 py-3 whitespace-nowrap text-zinc-500 font-sans">
                                        {{ $log->created_at->format('M d, Y H:i:s') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-sans font-semibold text-zinc-900">
                                        {{ $log->user->name ?? 'System' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="rounded bg-zinc-100 px-2 py-0.5 font-bold uppercase text-[10px] text-zinc-800">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap uppercase font-bold text-[11px] text-zinc-800">
                                        {{ $log->gateway ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase {{ $log->environment === 'live' ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                                            {{ $log->environment ?? 'sandbox' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-zinc-500">
                                        {{ $log->ip_address }}
                                    </td>
                                    <td class="px-4 py-3 text-zinc-600 font-sans max-w-xs truncate">
                                        @if(is_array($log->details))
                                            {{ json_encode($log->details) }}
                                        @else
                                            {{ $log->details ?: '—' }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="mt-8 rounded-xl border border-dashed border-zinc-200 p-8 text-center text-xs text-zinc-500">
                    No payment configuration changes recorded yet. Changes will be logged here with administrator identity and IP address.
                </div>
            @endif
        </div>

    </main>
</x-admin.layout>
