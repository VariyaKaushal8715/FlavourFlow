<x-admin.layout title="Business & Invoice Settings">
    <div class="px-4 py-8 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">Business Details &amp; Invoicing</h1>
                <p class="mt-1 text-sm text-zinc-500">
                    Configure your official merchant identity, GSTIN, invoice prefixes, and policies used on customer PDF invoices and order emails.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Dynamic PDF Engine Active
                </span>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-medium text-emerald-900 shadow-xs flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    <span>{{ session('status') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-300 bg-red-50 p-4 text-sm font-medium text-red-900 shadow-xs">
                <div class="flex items-start gap-2.5">
                    <svg class="h-5 w-5 text-red-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <div>
                        <p class="font-bold">Please correct the following errors:</p>
                        <ul class="mt-1 list-disc list-inside text-xs space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Form Container (2 Cols) --}}
            <div class="lg:col-span-2">
                <form action="{{ route('admin.business.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @method('PUT')

                    {{-- 1. Brand & Store Identity --}}
                    <div class="rounded-3xl border border-zinc-200 bg-white p-6 sm:p-8 shadow-xs">
                        <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 mb-6">
                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-red-50 text-brand-primary">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-zinc-950">Store &amp; Brand Identity</h2>
                                <p class="text-xs text-zinc-500">Official business name and logo displayed on invoices.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="store_name" class="block text-xs font-bold uppercase tracking-wider text-zinc-700">Store / Display Name <span class="text-red-600">*</span></label>
                                <input
                                    type="text"
                                    name="store_name"
                                    id="store_name"
                                    value="{{ old('store_name', $setting->store_name) }}"
                                    required
                                    class="mt-1.5 w-full rounded-2xl border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-950 shadow-xs focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                >
                            </div>

                            <div>
                                <label for="legal_name" class="block text-xs font-bold uppercase tracking-wider text-zinc-700">Legal / Registered Entity Name</label>
                                <input
                                    type="text"
                                    name="legal_name"
                                    id="legal_name"
                                    value="{{ old('legal_name', $setting->legal_name) }}"
                                    placeholder="e.g. FlavourFlow Pure Spices Pvt Ltd"
                                    class="mt-1.5 w-full rounded-2xl border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-950 shadow-xs focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                >
                            </div>

                            <div class="sm:col-span-2">
                                <label for="website_url" class="block text-xs font-bold uppercase tracking-wider text-zinc-700">Website URL</label>
                                <input
                                    type="url"
                                    name="website_url"
                                    id="website_url"
                                    value="{{ old('website_url', $setting->website_url) }}"
                                    placeholder="https://flavourflow.test"
                                    class="mt-1.5 w-full rounded-2xl border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-950 shadow-xs focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                >
                            </div>

                            {{-- Logo Upload --}}
                            <div class="sm:col-span-2 border-t border-zinc-100 pt-5 mt-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 mb-2">Store Logo (Invoice &amp; Branding)</label>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border border-zinc-200 bg-zinc-50 p-2 shadow-xs">
                                        <img src="{{ $setting->logoUrl() }}" alt="Current Logo" class="max-h-full max-w-full object-contain">
                                    </div>
                                    <div class="flex-1">
                                        <input
                                            type="file"
                                            name="logo"
                                            id="logo"
                                            accept="image/png,image/jpeg,image/webp"
                                            class="block w-full text-xs text-zinc-500 file:mr-4 file:rounded-xl file:border-0 file:bg-zinc-950 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-brand-primary"
                                        >
                                        <p class="mt-1.5 text-[11px] text-zinc-400">Accepted formats: PNG, JPG, WEBP. Max size: 2MB.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Contact Details & Business Address --}}
                    <div class="rounded-3xl border border-zinc-200 bg-white p-6 sm:p-8 shadow-xs">
                        <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 mb-6">
                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-amber-50 text-amber-700">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-zinc-950">Contact &amp; Physical Address</h2>
                                <p class="text-xs text-zinc-500">Contact information and location printed on customer invoices.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-zinc-700">Support Phone Number <span class="text-red-600">*</span></label>
                                <input
                                    type="text"
                                    name="phone"
                                    id="phone"
                                    value="{{ old('phone', $setting->phone) }}"
                                    required
                                    class="mt-1.5 w-full rounded-2xl border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-950 shadow-xs focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                >
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-zinc-700">Support / Billing Email <span class="text-red-600">*</span></label>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="{{ old('email', $setting->email) }}"
                                    required
                                    class="mt-1.5 w-full rounded-2xl border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-950 shadow-xs focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                >
                            </div>

                            <div class="sm:col-span-2">
                                <label for="business_address" class="block text-xs font-bold uppercase tracking-wider text-zinc-700">Primary Business / Dispatch Address <span class="text-red-600">*</span></label>
                                <textarea
                                    name="business_address"
                                    id="business_address"
                                    rows="2"
                                    required
                                    class="mt-1.5 w-full rounded-2xl border border-zinc-300 p-3.5 text-sm font-semibold text-zinc-950 shadow-xs focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                >{{ old('business_address', $setting->business_address) }}</textarea>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="billing_address" class="block text-xs font-bold uppercase tracking-wider text-zinc-700">Optional Registered Billing Address (Leave blank if same as above)</label>
                                <textarea
                                    name="billing_address"
                                    id="billing_address"
                                    rows="2"
                                    placeholder="Enter only if legal billing address differs from dispatch address"
                                    class="mt-1.5 w-full rounded-2xl border border-zinc-300 p-3.5 text-sm font-semibold text-zinc-950 shadow-xs focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                >{{ old('billing_address', $setting->billing_address) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Tax & Legal Compliance --}}
                    <div class="rounded-3xl border border-zinc-200 bg-white p-6 sm:p-8 shadow-xs">
                        <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 mb-6">
                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-blue-50 text-blue-700">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-zinc-950">Tax &amp; Legal Compliance</h2>
                                <p class="text-xs text-zinc-500">GSTIN registration and tax representation on invoices.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                            <div>
                                <label for="gstin" class="block text-xs font-bold uppercase tracking-wider text-zinc-700">GSTIN Number (Optional)</label>
                                <input
                                    type="text"
                                    name="gstin"
                                    id="gstin"
                                    value="{{ old('gstin', $setting->gstin) }}"
                                    placeholder="e.g. 24ABCDE1234F1Z5"
                                    maxlength="15"
                                    class="mt-1.5 w-full font-mono uppercase rounded-2xl border border-zinc-300 px-4 py-2.5 text-sm font-bold text-zinc-950 shadow-xs focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                >
                                <p class="mt-1 text-[11px] text-zinc-400">15-digit alphanumeric Indian GST number.</p>
                            </div>

                            <div>
                                <label for="tax_mode" class="block text-xs font-bold uppercase tracking-wider text-zinc-700">Tax Pricing Mode <span class="text-red-600">*</span></label>
                                <select
                                    name="tax_mode"
                                    id="tax_mode"
                                    class="mt-1.5 w-full rounded-2xl border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-950 shadow-xs focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                >
                                    <option value="inclusive" @selected(old('tax_mode', $setting->tax_mode) === 'inclusive')>Inclusive (Prices include GST)</option>
                                    <option value="none" @selected(old('tax_mode', $setting->tax_mode) === 'none')>None (Exempt / No Tax Specified)</option>
                                </select>
                            </div>

                            <div>
                                <label for="tax_rate" class="block text-xs font-bold uppercase tracking-wider text-zinc-700">Applicable Tax Rate (%)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    name="tax_rate"
                                    id="tax_rate"
                                    value="{{ old('tax_rate', $setting->tax_rate) }}"
                                    placeholder="0.00"
                                    class="mt-1.5 w-full rounded-2xl border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-950 shadow-xs focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                >
                                <p class="mt-1 text-[11px] text-zinc-400">Typical rate for spices: 5.00%</p>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Sequential Invoice Numbering --}}
                    <div class="rounded-3xl border border-zinc-200 bg-white p-6 sm:p-8 shadow-xs">
                        <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 mb-6">
                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-purple-50 text-purple-700">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-zinc-950">Sequential Invoice Numbering</h2>
                                <p class="text-xs text-zinc-500">Atomic, collision-free numbering format for official invoices.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="invoice_prefix" class="block text-xs font-bold uppercase tracking-wider text-zinc-700">Invoice Prefix <span class="text-red-600">*</span></label>
                                <input
                                    type="text"
                                    name="invoice_prefix"
                                    id="invoice_prefix"
                                    value="{{ old('invoice_prefix', $setting->invoice_prefix) }}"
                                    required
                                    maxlength="10"
                                    class="mt-1.5 w-full font-mono uppercase rounded-2xl border border-zinc-300 px-4 py-2.5 text-sm font-bold text-zinc-950 shadow-xs focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                >
                                <p class="mt-1 text-[11px] text-zinc-400">e.g. INV, FF-INV</p>
                            </div>

                            <div>
                                <label for="next_invoice_number" class="block text-xs font-bold uppercase tracking-wider text-zinc-700">Next Invoice Number <span class="text-red-600">*</span></label>
                                <input
                                    type="number"
                                    min="1"
                                    name="next_invoice_number"
                                    id="next_invoice_number"
                                    value="{{ old('next_invoice_number', $setting->next_invoice_number) }}"
                                    required
                                    class="mt-1.5 w-full font-mono rounded-2xl border border-zinc-300 px-4 py-2.5 text-sm font-bold text-zinc-950 shadow-xs focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                >
                                <p class="mt-1 text-[11px] text-zinc-400">Sequential counter incremented automatically per invoice.</p>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl bg-zinc-50 border border-zinc-200/80 p-4 flex items-center justify-between text-xs">
                            <span class="text-zinc-600 font-medium">Sample Next Invoice Number:</span>
                            <span class="font-mono font-bold text-brand-primary text-sm tracking-wider">
                                {{ strtoupper($setting->invoice_prefix ?: 'INV') }}-{{ now()->format('Y') }}-{{ sprintf('%04d', $setting->next_invoice_number ?: 1001) }}
                            </span>
                        </div>
                    </div>

                    {{-- 5. Invoice Notes, Instructions & Policies --}}
                    <div class="rounded-3xl border border-zinc-200 bg-white p-6 sm:p-8 shadow-xs">
                        <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 mb-6">
                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-50 text-emerald-700">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-zinc-950">Invoicing Notes, Instructions &amp; Policies</h2>
                                <p class="text-xs text-zinc-500">Custom terms and notes printed at the bottom of customer invoices.</p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label for="payment_instructions" class="block text-xs font-bold uppercase tracking-wider text-zinc-700">Payment Instructions / Reference Notice</label>
                                <textarea
                                    name="payment_instructions"
                                    id="payment_instructions"
                                    rows="2"
                                    class="mt-1.5 w-full rounded-2xl border border-zinc-300 p-3.5 text-sm font-semibold text-zinc-950 shadow-xs focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                >{{ old('payment_instructions', $setting->payment_instructions) }}</textarea>
                            </div>

                            <div>
                                <label for="return_policy_text" class="block text-xs font-bold uppercase tracking-wider text-zinc-700">Return &amp; Refund Policy Summary</label>
                                <textarea
                                    name="return_policy_text"
                                    id="return_policy_text"
                                    rows="2"
                                    class="mt-1.5 w-full rounded-2xl border border-zinc-300 p-3.5 text-sm font-semibold text-zinc-950 shadow-xs focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                >{{ old('return_policy_text', $setting->return_policy_text) }}</textarea>
                            </div>

                            <div>
                                <label for="invoice_footer" class="block text-xs font-bold uppercase tracking-wider text-zinc-700">Invoice Footer Note</label>
                                <textarea
                                    name="invoice_footer"
                                    id="invoice_footer"
                                    rows="2"
                                    class="mt-1.5 w-full rounded-2xl border border-zinc-300 p-3.5 text-sm font-semibold text-zinc-950 shadow-xs focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                >{{ old('invoice_footer', $setting->invoice_footer) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-2xl bg-zinc-950 px-8 py-3.5 text-sm font-bold text-white shadow-md transition hover:bg-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/20 active:scale-95"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Save Business &amp; Invoice Settings
                        </button>
                    </div>
                </form>
            </div>

            {{-- Side Live Invoice Preview Card (1 Col) --}}
            <div class="space-y-6">
                <div class="sticky top-6 rounded-3xl border border-zinc-200/90 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-900 border-b border-zinc-100 pb-3 flex items-center justify-between">
                        <span>Invoice Live Preview</span>
                        <span class="rounded bg-zinc-100 px-2 py-0.5 text-[10px] text-zinc-600 font-mono">PDF Preview</span>
                    </h3>

                    {{-- Mock Paper Invoice Preview Box --}}
                    <div class="mt-4 rounded-2xl border border-zinc-200 bg-zinc-50/50 p-4 text-[11px] font-sans shadow-inner space-y-4">
                        {{-- Top Red Strip --}}
                        <div class="h-1.5 w-full bg-brand-primary rounded-full"></div>

                        {{-- Header Row --}}
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-extrabold text-sm text-zinc-950">{{ $setting->store_name ?: 'FlavourFlow' }}</p>
                                @if($setting->legal_name)
                                    <p class="text-[9px] text-zinc-500 italic">{{ $setting->legal_name }}</p>
                                @endif
                                @if($setting->gstin)
                                    <p class="text-[10px] font-bold text-brand-primary mt-0.5">GSTIN: {{ $setting->gstin }}</p>
                                @endif
                                <p class="text-[10px] text-zinc-500 mt-1 max-w-[140px] leading-tight">{{ $setting->business_address }}</p>
                            </div>
                            <div class="text-right">
                                <span class="rounded bg-red-100 text-brand-primary px-1.5 py-0.5 text-[9px] font-bold">TAX INVOICE</span>
                                <p class="font-mono font-bold text-[10px] text-zinc-800 mt-1">{{ $setting->invoice_prefix }}-{{ now()->format('Y') }}-{{ sprintf('%04d', $setting->next_invoice_number) }}</p>
                                <p class="text-[9px] text-zinc-400">{{ now()->format('d M Y') }}</p>
                            </div>
                        </div>

                        {{-- Status Ribbon --}}
                        <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-2 py-1 text-[9px] font-bold text-emerald-800 flex items-center justify-between">
                            <span>STATUS: PAID &amp; VERIFIED</span>
                            <span class="font-mono text-zinc-500">REF: UPI-VERIFIED</span>
                        </div>

                        {{-- Customer Mock --}}
                        <div class="rounded-lg bg-white border border-zinc-200 p-2 text-[10px]">
                            <p class="text-[9px] font-bold text-zinc-400 uppercase">Billed &amp; Shipped To:</p>
                            <p class="font-bold text-zinc-800">Aarav Patel</p>
                            <p class="text-zinc-500 text-[9px]">Ahmedabad, Gujarat - 380009</p>
                        </div>

                        {{-- Items Snippet --}}
                        <div class="border-t border-zinc-200 pt-2 space-y-1">
                            <div class="flex justify-between text-zinc-700 font-medium">
                                <span>Royal Garam Masala (100g)</span>
                                <span>Rs. 150.00</span>
                            </div>
                            <div class="flex justify-between text-zinc-700 font-medium">
                                <span>Red Chilli Powder (100g)</span>
                                <span>Rs. 99.00</span>
                            </div>
                            <div class="border-t border-dashed border-zinc-300 pt-1.5 flex justify-between font-bold text-zinc-950 text-xs">
                                <span>Total Amount:</span>
                                <span class="text-brand-primary">Rs. 249.00</span>
                            </div>
                        </div>

                        {{-- Footer Preview --}}
                        <div class="border-t border-zinc-200 pt-2 text-[9px] text-zinc-400 text-center italic">
                            {{ Str::limit($setting->invoice_footer ?: 'Thank you for choosing FlavourFlow!', 60) }}
                        </div>
                    </div>

                    {{-- Reset Logo Action --}}
                    @if ($setting->hasCustomLogo())
                        <div class="mt-4 pt-4 border-t border-zinc-100">
                            <form action="{{ route('admin.business.settings.logo.destroy') }}" method="POST" onsubmit="return confirm('Reset store logo to default brand mark?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full text-center text-xs font-semibold text-red-600 hover:text-red-800 py-1.5 transition">
                                    Reset Logo to Default Brand Mark
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin.layout>

