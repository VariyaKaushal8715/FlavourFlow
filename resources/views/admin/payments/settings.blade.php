<x-admin.layout title="Payment Settings">
    <main class="mx-auto w-full max-w-5xl px-4 py-8 sm:px-6 lg:px-8" x-data="{ submitting: false, showSecret: false }">

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
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">Razorpay Payment Settings</h1>
                    @if ($isConfigured)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            {{ strtoupper($mode) }} MODE
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            SETUP REQUIRED
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-zinc-500">Manage Razorpay Key ID and Secret securely from the admin panel. Credentials are validated and written to project configuration.</p>
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
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900 shadow-sm">
                <div class="flex items-center gap-2 font-bold text-red-800">
                    <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    <span>Configuration update failed:</span>
                </div>
                <ul class="mt-2 list-inside list-disc text-xs text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <p class="mt-3 text-xs text-red-800 font-medium">Fail-Safe Active: Previous valid credentials remain intact.</p>
            </div>
        @endif

        {{-- Status Overview Cards --}}
        <div class="mb-8 grid gap-4 sm:grid-cols-3">
            {{-- Gateway Status Card --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Gateway Status</span>
                    @if ($isConfigured)
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-100"></span>
                    @else
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500 ring-4 ring-amber-100"></span>
                    @endif
                </div>
                <p class="mt-3 text-xl font-bold text-zinc-950">
                    {{ $isConfigured ? 'Ready & Active' : 'Not Configured' }}
                </p>
                <p class="mt-1 text-xs text-zinc-500">
                    {{ $isConfigured ? 'Orders & signatures processed live via Razorpay' : 'Checkout will run in simulated test mode' }}
                </p>
            </div>

            {{-- Environment Mode Card --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Active Mode</span>
                    <span class="rounded bg-zinc-100 px-2 py-0.5 text-[10px] font-bold text-zinc-700">{{ strtoupper($mode) }}</span>
                </div>
                <p class="mt-3 text-xl font-bold text-zinc-950">
                    {{ $mode === 'live' ? 'Live Production' : 'Test Mode' }}
                </p>
                <p class="mt-1 text-xs text-zinc-500">
                    {{ $mode === 'live' ? 'Real currency (INR) will be billed to customers' : 'Simulated payments via Razorpay test cards' }}
                </p>
            </div>

            {{-- Credential Protection Card --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Key Secret Status</span>
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                </div>
                <p class="mt-3 text-xl font-bold font-mono tracking-widest text-zinc-950">
                    {{ $hasConfiguredSecret ? '••••••••••••••••' : 'Not Set' }}
                </p>
                <p class="mt-1 text-xs text-zinc-500">
                    {{ $hasConfiguredSecret ? 'Masked on server; never output in plain text' : 'Requires key secret from Razorpay Dashboard' }}
                </p>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="border-b border-zinc-100 pb-5">
                <h2 class="text-lg font-bold text-zinc-950">Manage Razorpay API Credentials</h2>
                <p class="mt-1 text-xs text-zinc-500">
                    Update your API credentials below. Credentials will be verified directly with Razorpay's API before updating the configuration file.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.payments.settings.update') }}" class="mt-6 space-y-6" @submit="submitting = true">
                @csrf
                @method('PUT')

                {{-- Key ID Field --}}
                <div>
                    <div class="flex items-center justify-between">
                        <label for="key_id" class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                            Razorpay Key ID <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[11px] text-zinc-400">Starts with rzp_test_ or rzp_live_</span>
                    </div>
                    <div class="relative mt-2">
                        <input
                            type="text"
                            id="key_id"
                            name="key_id"
                            value="{{ old('key_id', $keyId) }}"
                            placeholder="e.g. rzp_test_1234567890abcdef"
                            required
                            autocomplete="off"
                            spellcheck="false"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 font-mono text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-red-600 focus:ring-2 focus:ring-red-600/10"
                        >
                    </div>
                    @error('key_id')
                        <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-zinc-500">
                        Public Key identifier generated in Razorpay Dashboard &rarr; Account & Settings &rarr; API Keys.
                    </p>
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
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Secret Configured (Masked)
                            </span>
                        @endif
                    </div>
                    <div class="relative mt-2">
                        <input
                            :type="showSecret ? 'text' : 'password'"
                            id="key_secret"
                            name="key_secret"
                            value=""
                            autocomplete="new-password"
                            spellcheck="false"
                            placeholder="{{ $hasConfiguredSecret ? 'Leave blank to keep current secret, or enter new secret' : 'Enter 24-character Razorpay Key Secret' }}"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 pr-12 font-mono text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-red-600 focus:ring-2 focus:ring-red-600/10"
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
                    <p class="mt-1.5 text-xs text-zinc-500">
                        @if ($hasConfiguredSecret)
                            Existing secret is securely protected in <code class="rounded bg-zinc-100 px-1 py-0.5 text-zinc-700">.env</code>. Leave blank to keep existing secret, or enter a new secret to rotate it.
                        @else
                            Confidential key provided when generating API keys in the Razorpay dashboard. Never share this secret.
                        @endif
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col gap-3 pt-4 sm:flex-row sm:items-center sm:justify-between border-t border-zinc-100">
                    <div class="flex items-center gap-2 text-xs text-zinc-500">
                        <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                        <span>Atomic updates with automatic configuration backup</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.payments.index') }}" class="rounded-xl border border-zinc-300 px-5 py-2.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 transition">
                            Cancel
                        </a>
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-950 px-6 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 disabled:opacity-50"
                        >
                            <span x-show="!submitting">Validate & Save Credentials</span>
                            <span x-show="submitting" x-cloak class="inline-flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Verifying with Razorpay...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- API Verification & Connectivity Check Card --}}
        @if ($isConfigured)
            <div class="mt-8 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-950">Live Credential Authentication Check</h3>
                        <p class="mt-0.5 text-xs text-zinc-500">
                            Perform a non-intrusive server-side authentication test against Razorpay API to confirm your active credentials without charging any customer.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.payments.settings.test') }}">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs font-semibold text-zinc-800 shadow-sm transition hover:bg-zinc-50 hover:text-zinc-950"
                        >
                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Test API Connection
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Guide & Reference Information --}}
        <div class="mt-8 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-6 text-xs text-zinc-600">
            <h4 class="font-bold text-zinc-900 uppercase tracking-wider text-[11px]">Razorpay Integration Best Practices</h4>
            <div class="mt-3 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="font-semibold text-zinc-800">Test Mode (Sandbox):</p>
                    <p class="mt-1 leading-relaxed text-zinc-500">
                        Keys starting with <code class="rounded bg-white px-1 py-0.5 text-zinc-700 ring-1 ring-zinc-200">rzp_test_</code> process test transactions. Razorpay provides standard test cards (e.g. Card: <code class="rounded bg-white px-1 py-0.5 text-zinc-700">4111 1111 1111 1111</code>, CVV: <code class="rounded bg-white px-1 py-0.5 text-zinc-700">123</code>, OTP: <code class="rounded bg-white px-1 py-0.5 text-zinc-700">123456</code>).
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-zinc-800">Live Production Mode:</p>
                    <p class="mt-1 leading-relaxed text-zinc-500">
                        Keys starting with <code class="rounded bg-white px-1 py-0.5 text-zinc-700 ring-1 ring-zinc-200">rzp_live_</code> process real customer payments with immediate settlement to your linked bank account.
                    </p>
                </div>
            </div>
            <div class="mt-4 border-t border-zinc-200/80 pt-3 flex flex-wrap items-center justify-between gap-2 text-[11px] text-zinc-500">
                <span>Webhook URL: <code class="font-mono text-zinc-700">{{ url('/api/webhooks/cashfree') }}</code> / <code class="font-mono text-zinc-700">{{ url('/checkout/razorpay/verify') }}</code></span>
                <span>Config Target: <code class="font-mono text-zinc-700">.env &rarr; RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET</code></span>
            </div>
        </div>

    </main>
</x-admin.layout>
