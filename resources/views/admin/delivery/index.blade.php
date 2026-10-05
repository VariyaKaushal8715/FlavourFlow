<x-admin.layout title="Delivery Control">
    <div class="mx-auto max-w-6xl space-y-8 pb-24">
        {{-- Header Section --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-red-600">
                    <span>Admin Controls</span>
                    <span>&bull;</span>
                    <span>Fulfillment & Logistics</span>
                </div>
                <h1 class="mt-1 text-2xl font-bold text-zinc-950 sm:text-3xl">Delivery Control</h1>
                <p class="mt-1 text-sm text-zinc-600">
                    Configure deliverable regions, restrict shipping to custom locations, and manage worldwide delivery settings.
                </p>
            </div>

            {{-- Live Status Pill --}}
            <div class="flex items-center gap-3 self-start rounded-2xl border border-zinc-200 bg-white px-4 py-2.5 shadow-sm sm:self-auto">
                <div class="relative flex h-3 w-3">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-xs">
                    <span class="font-medium text-zinc-500">Live Setting:</span>
                    <span class="ml-1 font-bold text-zinc-900" id="live-status-label">
                        @if ($setting->mode === 'all')
                            Deliver Everywhere
                        @else
                            Custom Locations ({{ count($setting->custom_locations ?? []) }} active)
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-red-900 shadow-sm" id="error-alert">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm font-semibold text-red-950">
                        <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                        <span>Please correct the errors below:</span>
                    </div>
                    <button type="button" onclick="document.getElementById('error-alert').remove()" class="rounded-lg p-1 text-red-600 hover:bg-red-100">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <ul class="mt-2 list-inside list-disc text-xs text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Main Settings Form --}}
        <form action="{{ route('admin.delivery.update') }}" method="POST" id="delivery-control-form" class="space-y-8">
            @csrf
            @method('PUT')

            {{-- Delivery Mode Cards --}}
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-5">
                    <div>
                        <h2 class="text-lg font-bold text-zinc-950">Delivery Mode</h2>
                        <p class="mt-0.5 text-xs text-zinc-500">Choose whether all customer addresses are accepted or only specific regions.</p>
                    </div>
                    <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-600">Step 1</span>
                </div>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    {{-- Option 1: Deliver Everywhere --}}
                    <label
                        class="group relative flex cursor-pointer flex-col justify-between rounded-2xl border-2 p-5 transition-all duration-200 hover:shadow-md"
                        id="card-mode-all"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition group-hover:bg-blue-600 group-hover:text-white">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                                </svg>
                            </div>
                            <input
                                type="radio"
                                name="mode"
                                value="all"
                                class="h-5 w-5 text-red-600 focus:ring-red-500"
                                id="radio-mode-all"
                                {{ old('mode', $setting->mode) === 'all' ? 'checked' : '' }}
                            >
                        </div>
                        <div class="mt-4">
                            <h3 class="text-base font-bold text-zinc-950">Deliver Everywhere</h3>
                            <p class="mt-1 text-xs leading-relaxed text-zinc-500">
                                Open worldwide delivery. Customers from any country, state, or city can successfully check location and complete checkout.
                            </p>
                        </div>
                        <div class="mt-4 flex items-center gap-1.5 text-xs font-semibold text-blue-700">
                            <span>Unrestricted delivery</span>
                            <span class="text-lg leading-none">&rarr;</span>
                        </div>
                    </label>

                    {{-- Option 2: Custom Locations --}}
                    <label
                        class="group relative flex cursor-pointer flex-col justify-between rounded-2xl border-2 p-5 transition-all duration-200 hover:shadow-md"
                        id="card-mode-custom"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 transition group-hover:bg-amber-600 group-hover:text-white">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                            </div>
                            <input
                                type="radio"
                                name="mode"
                                value="custom"
                                class="h-5 w-5 text-red-600 focus:ring-red-500"
                                id="radio-mode-custom"
                                {{ old('mode', $setting->mode) === 'custom' ? 'checked' : '' }}
                            >
                        </div>
                        <div class="mt-4">
                            <h3 class="text-base font-bold text-zinc-950">Custom Locations</h3>
                            <p class="mt-1 text-xs leading-relaxed text-zinc-500">
                                Restrict shipping to selected countries, states, and cities. Only locations in your allowed list can place orders.
                            </p>
                        </div>
                        <div class="mt-4 flex items-center gap-1.5 text-xs font-semibold text-amber-700">
                            <span>Granular location controls</span>
                            <span class="text-lg leading-none">&rarr;</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Custom Locations Configurator (collapsible / animated) --}}
            <div id="custom-locations-panel" class="transition-all duration-300">
                <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-100 pb-5">
                        <div>
                            <h2 class="text-lg font-bold text-zinc-950">Configure Custom Deliverable Locations</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">
                                Select Country &rarr; State &rarr; City. State and City smoothly hide for international countries.
                            </p>
                        </div>
                        <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-600">Step 2</span>
                    </div>

                    {{-- Location Builder Controls --}}
                    <div class="mt-6 rounded-2xl border border-amber-200/70 bg-gradient-to-br from-amber-50/40 via-white to-amber-50/20 p-5 sm:p-6">
                        <p class="text-xs font-bold uppercase tracking-wider text-amber-800">Add New Allowed Location</p>

                        <div class="mt-4 grid gap-4 sm:grid-cols-3" id="location-builder-grid">
                            {{-- Country Selector --}}
                            <div>
                                <label for="select-country" class="block text-xs font-bold text-zinc-700">Country <span class="text-red-500">*</span></label>
                                <select
                                    id="select-country"
                                    class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm font-medium text-zinc-900 shadow-sm transition hover:border-zinc-400 focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20"
                                >
                                    @foreach ($countries as $country)
                                        <option value="{{ $country }}" {{ $country === 'India' ? 'selected' : '' }}>{{ $country }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- State Selector (India only - animated collapse) --}}
                            <div id="state-wrapper" class="transition-all duration-300">
                                <label for="select-state" class="block text-xs font-bold text-zinc-700">State / Region <span class="text-red-500">*</span></label>
                                <select
                                    id="select-state"
                                    class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm font-medium text-zinc-900 shadow-sm transition hover:border-zinc-400 focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20"
                                >
                                    <option value="">-- All States (Entire India) --</option>
                                    @foreach (array_keys($indiaStatesWithCities) as $state)
                                        <option value="{{ $state }}">{{ $state }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- City Selector (India only - animated collapse) --}}
                            <div id="city-wrapper" class="transition-all duration-300">
                                <label for="select-city" class="block text-xs font-bold text-zinc-700">City <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        id="input-city"
                                        list="city-datalist"
                                        placeholder="Type or select city (optional for entire state)"
                                        class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm font-medium text-zinc-900 shadow-sm transition hover:border-zinc-400 focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20"
                                    />
                                    <datalist id="city-datalist">
                                        {{-- Populated dynamically via JS when state changes --}}
                                    </datalist>
                                </div>
                            </div>
                        </div>

                        {{-- Helper info text for country mode --}}
                        <div id="country-info-note" class="mt-3 flex items-center gap-2 text-xs text-zinc-500">
                            <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                            <span id="country-info-text">India location: Specify State and City or allow delivery across the entire state.</span>
                        </div>

                        {{-- Add Location Button --}}
                        <div class="mt-4 flex justify-end">
                            <button
                                type="button"
                                id="btn-add-location"
                                class="inline-flex items-center gap-2 rounded-xl bg-zinc-950 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-zinc-800 active:scale-[0.98]"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                <span>Add Location Rule</span>
                            </button>
                        </div>
                    </div>

                    {{-- Staged Custom Locations Table --}}
                    <div class="mt-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-zinc-950">Active Deliverable Location Rules (<span id="location-count">0</span>)</h3>
                            <button
                                type="button"
                                id="btn-clear-all-locations"
                                class="text-xs font-semibold text-red-600 hover:text-red-800 transition"
                            >
                                Clear All
                            </button>
                        </div>

                        <div class="mt-3 overflow-hidden rounded-2xl border border-zinc-200 bg-white">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-zinc-600">
                                    <thead class="border-b border-zinc-200 bg-zinc-50 font-bold uppercase tracking-wider text-zinc-700">
                                        <tr>
                                            <th class="px-4 py-3">#</th>
                                            <th class="px-4 py-3">Country</th>
                                            <th class="px-4 py-3">State / Region</th>
                                            <th class="px-4 py-3">City</th>
                                            <th class="px-4 py-3">Coverage Scope</th>
                                            <th class="px-4 py-3 text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="locations-table-body" class="divide-y divide-zinc-100 font-medium">
                                        {{-- Populated by JavaScript --}}
                                    </tbody>
                                </table>
                            </div>

                            {{-- Empty Table State --}}
                            <div id="locations-empty-state" class="hidden p-8 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                </div>
                                <p class="mt-3 text-sm font-semibold text-zinc-900">No Custom Locations Added Yet</p>
                                <p class="mt-1 text-xs text-zinc-500">Use the selector above to add countries, states, and cities to your deliverable regions.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hidden inputs container for form submission --}}
            <div id="hidden-locations-inputs"></div>
        </form>

        {{-- Floating Minimal Apply Changes Popup / Pill (Appears only on change) --}}
        <div
            id="apply-changes-popup"
            class="fixed bottom-6 right-6 z-50 flex items-center gap-3.5 rounded-2xl border border-zinc-800 bg-zinc-950/95 px-5 py-3.5 text-white shadow-[0_20px_50px_rgba(0,0,0,0.4)] backdrop-blur-xl ring-1 ring-white/20 transition-all duration-300 ease-out transform opacity-0 translate-y-6 pointer-events-none scale-95"
            role="dialog"
            aria-live="polite"
        >
            <div class="flex items-center gap-2.5">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                </span>
                <span class="text-xs font-medium text-zinc-300">Unsaved changes</span>
            </div>

            <div class="h-4 w-px bg-zinc-800"></div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    id="btn-discard-draft"
                    class="rounded-xl px-3 py-1.5 text-xs font-semibold text-zinc-400 transition hover:bg-white/10 hover:text-white"
                >
                    Discard
                </button>
                <button
                    type="button"
                    id="btn-apply-popup"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-red-600 px-4 py-1.5 text-xs font-bold text-white shadow-md shadow-red-600/30 transition hover:bg-red-500 active:scale-95"
                >
                    <span id="apply-spinner" class="hidden h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    <svg id="apply-check-icon" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    <span>Apply Changes</span>
                </button>
            </div>
        </div>

        {{-- Floating Minimal Success Toast Notification (Auto-hides) --}}
        <div
            id="success-toast"
            class="fixed bottom-6 left-1/2 z-50 flex -translate-x-1/2 items-center gap-2.5 rounded-2xl border border-emerald-500/30 bg-emerald-950/95 px-5 py-3 text-white shadow-2xl backdrop-blur-xl transition-all duration-300 ease-out transform opacity-0 translate-y-6 pointer-events-none scale-95"
            role="status"
            aria-live="polite"
        >
            <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            </div>
            <span class="text-xs font-bold text-emerald-100" id="success-toast-message">Changes successfully applied.</span>
        </div>
    </div>

    {{-- Script for UI state, cascading dropdowns, smooth animations & Apply Changes --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const initialLocations = @json(old('locations', $setting->custom_locations ?? []));
            const statesWithCities = @json($indiaStatesWithCities);

            let stagedLocations = Array.isArray(initialLocations) ? [...initialLocations] : [];
            let activeDatabaseState = JSON.stringify({
                mode: "{{ $setting->mode }}",
                locations: initialLocations
            });

            const radioAll = document.getElementById('radio-mode-all');
            const radioCustom = document.getElementById('radio-mode-custom');
            const cardAll = document.getElementById('card-mode-all');
            const cardCustom = document.getElementById('card-mode-custom');
            const customLocationsPanel = document.getElementById('custom-locations-panel');

            const selectCountry = document.getElementById('select-country');
            const stateWrapper = document.getElementById('state-wrapper');
            const selectState = document.getElementById('select-state');
            const cityWrapper = document.getElementById('city-wrapper');
            const inputCity = document.getElementById('input-city');
            const cityDatalist = document.getElementById('city-datalist');
            const countryInfoText = document.getElementById('country-info-text');

            const btnAddLocation = document.getElementById('btn-add-location');
            const btnClearAll = document.getElementById('btn-clear-all-locations');
            const tableBody = document.getElementById('locations-table-body');
            const emptyState = document.getElementById('locations-empty-state');
            const locationCount = document.getElementById('location-count');
            const hiddenInputsContainer = document.getElementById('hidden-locations-inputs');
            const liveStatusLabel = document.getElementById('live-status-label');

            const applyPopup = document.getElementById('apply-changes-popup');
            const btnApplyPopup = document.getElementById('btn-apply-popup');
            const btnDiscardDraft = document.getElementById('btn-discard-draft');
            const applySpinner = document.getElementById('apply-spinner');
            const applyCheckIcon = document.getElementById('apply-check-icon');

            const successToast = document.getElementById('success-toast');
            const successToastMessage = document.getElementById('success-toast-message');
            let successToastTimeout = null;

            function updateModeCards() {
                const isCustom = radioCustom.checked;

                if (isCustom) {
                    cardCustom.classList.add('border-amber-500', 'bg-amber-50/20', 'ring-2', 'ring-amber-500/20');
                    cardCustom.classList.remove('border-zinc-200');
                    cardAll.classList.remove('border-blue-500', 'bg-blue-50/20', 'ring-2', 'ring-blue-500/20');
                    cardAll.classList.add('border-zinc-200');
                    customLocationsPanel.classList.remove('hidden', 'opacity-0', 'scale-95');
                    customLocationsPanel.style.display = 'block';
                } else {
                    cardAll.classList.add('border-blue-500', 'bg-blue-50/20', 'ring-2', 'ring-blue-500/20');
                    cardAll.classList.remove('border-zinc-200');
                    cardCustom.classList.remove('border-amber-500', 'bg-amber-50/20', 'ring-2', 'ring-amber-500/20');
                    cardCustom.classList.add('border-zinc-200');
                    customLocationsPanel.classList.add('hidden');
                    customLocationsPanel.style.display = 'none';
                }
                syncHiddenInputs();
                evaluateChanges();
            }

            function handleCountryChange() {
                const country = selectCountry.value;
                const isIndia = country.toLowerCase() === 'india';

                if (isIndia) {
                    stateWrapper.style.display = 'block';
                    cityWrapper.style.display = 'block';
                    stateWrapper.classList.remove('opacity-0', 'scale-95');
                    cityWrapper.classList.remove('opacity-0', 'scale-95');
                    countryInfoText.textContent = 'India location: Specify State and City or allow delivery across the entire state.';
                } else {
                    stateWrapper.style.display = 'none';
                    cityWrapper.style.display = 'none';
                    countryInfoText.textContent = `${country}: International delivery will cover the entire country.`;
                }
            }

            function handleStateChange() {
                const state = selectState.value;
                cityDatalist.innerHTML = '';
                inputCity.value = '';

                if (state && statesWithCities[state]) {
                    statesWithCities[state].forEach(city => {
                        const opt = document.createElement('option');
                        opt.value = city;
                        cityDatalist.appendChild(opt);
                    });
                }
            }

            function renderLocationsTable() {
                tableBody.innerHTML = '';
                locationCount.textContent = stagedLocations.length;

                if (stagedLocations.length === 0) {
                    emptyState.classList.remove('hidden');
                } else {
                    emptyState.classList.add('hidden');

                    stagedLocations.forEach((loc, index) => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-zinc-50 transition group';

                        const isIndia = (loc.country || '').toLowerCase() === 'india';
                        let scopeBadge = '';

                        if (!isIndia) {
                            scopeBadge = '<span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800">Nationwide (Intl)</span>';
                        } else if (!loc.state) {
                            scopeBadge = '<span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">Entire India</span>';
                        } else if (!loc.city) {
                            scopeBadge = `<span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800">All ${loc.state}</span>`;
                        } else {
                            scopeBadge = '<span class="inline-flex rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-bold text-purple-800">City Specific</span>';
                        }

                        tr.innerHTML = `
                            <td class="px-4 py-3 text-zinc-400 font-mono">${index + 1}</td>
                            <td class="px-4 py-3 font-semibold text-zinc-950">${loc.country || '-'}</td>
                            <td class="px-4 py-3 text-zinc-700">${loc.state || '<span class="text-zinc-400 italic">All States</span>'}</td>
                            <td class="px-4 py-3 text-zinc-700">${loc.city || '<span class="text-zinc-400 italic">All Cities</span>'}</td>
                            <td class="px-4 py-3">${scopeBadge}</td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" data-remove-index="${index}" class="rounded-lg p-1 text-zinc-400 hover:bg-red-50 hover:text-red-600 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                </button>
                            </td>
                        `;
                        tableBody.appendChild(tr);
                    });
                }

                syncHiddenInputs();
                evaluateChanges();
            }

            function syncHiddenInputs() {
                hiddenInputsContainer.innerHTML = '';
                stagedLocations.forEach((loc, index) => {
                    const inputCountry = document.createElement('input');
                    inputCountry.type = 'hidden';
                    inputCountry.name = `locations[${index}][country]`;
                    inputCountry.value = loc.country;
                    hiddenInputsContainer.appendChild(inputCountry);

                    if (loc.state) {
                        const inputState = document.createElement('input');
                        inputState.type = 'hidden';
                        inputState.name = `locations[${index}][state]`;
                        inputState.value = loc.state;
                        hiddenInputsContainer.appendChild(inputState);
                    }

                    if (loc.city) {
                        const inputCityElem = document.createElement('input');
                        inputCityElem.type = 'hidden';
                        inputCityElem.name = `locations[${index}][city]`;
                        inputCityElem.value = loc.city;
                        hiddenInputsContainer.appendChild(inputCityElem);
                    }
                });
            }

            function evaluateChanges() {
                const currentMode = radioAll.checked ? 'all' : 'custom';
                const currentState = JSON.stringify({
                    mode: currentMode,
                    locations: stagedLocations
                });

                if (currentState !== activeDatabaseState) {
                    showApplyPopup();
                } else {
                    hideApplyPopup();
                }
            }

            function showApplyPopup() {
                applyPopup.classList.remove('opacity-0', 'translate-y-6', 'pointer-events-none', 'scale-95');
                applyPopup.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto', 'scale-100');
            }

            function hideApplyPopup() {
                applyPopup.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto', 'scale-100');
                applyPopup.classList.add('opacity-0', 'translate-y-6', 'pointer-events-none', 'scale-95');
            }

            function showSuccessToast(message = 'Changes successfully applied.') {
                clearTimeout(successToastTimeout);
                successToastMessage.textContent = message;

                successToast.classList.remove('opacity-0', 'translate-y-6', 'pointer-events-none', 'scale-95');
                successToast.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto', 'scale-100');

                successToastTimeout = setTimeout(() => {
                    successToast.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto', 'scale-100');
                    successToast.classList.add('opacity-0', 'translate-y-6', 'pointer-events-none', 'scale-95');
                }, 3500);
            }

            async function submitApplyChanges() {
                btnApplyPopup.disabled = true;
                applySpinner.classList.remove('hidden');
                applyCheckIcon.classList.add('hidden');

                const currentMode = radioAll.checked ? 'all' : 'custom';
                const payload = {
                    mode: currentMode,
                    locations: stagedLocations
                };

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const response = await fetch('{{ route('admin.delivery.update') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-HTTP-Method-Override': 'PUT'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Immediately remove the popup button
                        hideApplyPopup();

                        // Update current baseline active state
                        activeDatabaseState = JSON.stringify({
                            mode: currentMode,
                            locations: stagedLocations
                        });

                        // Update Live Setting status header
                        if (currentMode === 'all') {
                            liveStatusLabel.textContent = 'Deliver Everywhere';
                        } else {
                            liveStatusLabel.textContent = `Custom Locations (${stagedLocations.length} active)`;
                        }

                        // Show minimal success toast
                        showSuccessToast(data.message || 'Changes successfully applied.');
                    } else {
                        alert(data.message || 'Error saving delivery settings.');
                    }
                } catch (err) {
                    console.error('Apply changes error:', err);
                    alert('Error saving delivery settings. Please try again.');
                } finally {
                    btnApplyPopup.disabled = false;
                    applySpinner.classList.add('hidden');
                    applyCheckIcon.classList.remove('hidden');
                }
            }

            function discardDraftChanges() {
                try {
                    const saved = JSON.parse(activeDatabaseState);
                    if (saved.mode === 'all') {
                        radioAll.checked = true;
                    } else {
                        radioCustom.checked = true;
                    }
                    stagedLocations = Array.isArray(saved.locations) ? [...saved.locations] : [];
                    updateModeCards();
                    renderLocationsTable();
                    hideApplyPopup();
                } catch (e) {
                    window.location.reload();
                }
            }

            // Event Listeners
            radioAll.addEventListener('change', updateModeCards);
            radioCustom.addEventListener('change', updateModeCards);
            selectCountry.addEventListener('change', handleCountryChange);
            selectState.addEventListener('change', handleStateChange);

            btnAddLocation.addEventListener('click', () => {
                const country = selectCountry.value.trim();
                if (!country) return;

                const isIndia = country.toLowerCase() === 'india';
                const state = isIndia ? selectState.value.trim() : null;
                const city = isIndia ? inputCity.value.trim() : null;

                // Check duplicate
                const exists = stagedLocations.some(l => 
                    (l.country || '').toLowerCase() === country.toLowerCase() &&
                    (l.state || '').toLowerCase() === (state || '').toLowerCase() &&
                    (l.city || '').toLowerCase() === (city || '').toLowerCase()
                );

                if (exists) {
                    alert('This location rule is already in your staged list.');
                    return;
                }

                stagedLocations.push({ country, state, city });
                inputCity.value = '';
                renderLocationsTable();
            });

            tableBody.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-remove-index]');
                if (!btn) return;
                const index = parseInt(btn.getAttribute('data-remove-index'), 10);
                if (!isNaN(index) && index >= 0 && index < stagedLocations.length) {
                    stagedLocations.splice(index, 1);
                    renderLocationsTable();
                }
            });

            btnClearAll.addEventListener('click', () => {
                if (stagedLocations.length === 0) return;
                if (confirm('Clear all staged custom locations?')) {
                    stagedLocations = [];
                    renderLocationsTable();
                }
            });

            btnApplyPopup.addEventListener('click', submitApplyChanges);
            btnDiscardDraft.addEventListener('click', discardDraftChanges);

            // Init
            updateModeCards();
            handleCountryChange();
            renderLocationsTable();
            hideApplyPopup();

            @if (session('status'))
                showSuccessToast("{{ session('status') }}");
            @endif
        });
    </script>
</x-admin.layout>
