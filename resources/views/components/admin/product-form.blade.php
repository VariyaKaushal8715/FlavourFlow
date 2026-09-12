@props([
    'action',
    'product' => null,
    'submitLabel' => 'Save product',
])

@php
    $isEditing = $product !== null;
    $highlightValue = old('highlights', $product?->highlights ?? []);
    $highlightValue = is_array($highlightValue) ? implode(PHP_EOL, $highlightValue) : $highlightValue;
@endphp

<form class="mt-6 space-y-4" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="name">Product name</label>
            <input class="admin-input" id="name" name="name" type="text" value="{{ old('name', $product?->name) }}" required>
            @error('name') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="sku">SKU</label>
            <input class="admin-input uppercase" id="sku" name="sku" type="text" value="{{ old('sku', $product?->sku) }}" placeholder="FF-GM-100" required>
            @error('sku') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="category">Category</label>
            <input class="admin-input" id="category" name="category" type="text" value="{{ old('category', $product?->category) }}" placeholder="Pure spice" required>
            @error('category') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="unit">Pack size</label>
            <input class="admin-input" id="unit" name="unit" type="text" value="{{ old('unit', $product?->unit ?? '100 g') }}" placeholder="100 g" required>
            @error('unit') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="text-sm font-semibold text-zinc-800" for="description">Description</label>
        <textarea class="admin-input min-h-24 py-3" id="description" name="description" required>{{ old('description', $product?->description) }}</textarea>
        <p class="mt-1 text-xs text-zinc-500">Short, attractive copy used on product cards.</p>
        @error('description') <p class="admin-error">{{ $message }}</p> @enderror
    </div>

    <div class="border-t border-zinc-200 pt-5">
        <p class="text-xs font-semibold uppercase text-emerald-700">Full product page</p>
        <p class="mt-1 text-xs leading-5 text-zinc-500">These details appear only after a customer opens the product.</p>
    </div>

    <div>
        <label class="text-sm font-semibold text-zinc-800" for="long_description">Full description</label>
        <textarea class="admin-input min-h-32 py-3" id="long_description" name="long_description">{{ old('long_description', $product?->long_description) }}</textarea>
        @error('long_description') <p class="admin-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-zinc-800" for="highlights">Product highlights</label>
        <textarea class="admin-input min-h-28 py-3" id="highlights" name="highlights" placeholder="Freshly packed&#10;No artificial colours&#10;Balanced everyday flavour">{{ $highlightValue }}</textarea>
        <p class="mt-1 text-xs text-zinc-500">Add one highlight per line, up to six.</p>
        @error('highlights') <p class="admin-error">{{ $message }}</p> @enderror
        @error('highlights.*') <p class="admin-error">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="ingredients">Ingredients</label>
            <textarea class="admin-input min-h-28 py-3" id="ingredients" name="ingredients">{{ old('ingredients', $product?->ingredients) }}</textarea>
            @error('ingredients') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="usage_instructions">How to use</label>
            <textarea class="admin-input min-h-28 py-3" id="usage_instructions" name="usage_instructions">{{ old('usage_instructions', $product?->usage_instructions) }}</textarea>
            @error('usage_instructions') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="text-sm font-semibold text-zinc-800" for="origin">Origin / packed at</label>
        <input class="admin-input" id="origin" name="origin" type="text" value="{{ old('origin', $product?->origin) }}" placeholder="Blended and packed in Gujarat, India">
        @error('origin') <p class="admin-error">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="price">Selling price</label>
            <input class="admin-input" id="price" name="price" type="number" value="{{ old('price', $product?->price) }}" min="0" step="0.01" required>
            @error('price') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="compare_at_price">MRP / compare price</label>
            <input class="admin-input" id="compare_at_price" name="compare_at_price" type="number" value="{{ old('compare_at_price', $product?->compare_at_price) }}" min="0" step="0.01">
            @error('compare_at_price') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="quantity">Quantity</label>
            <input class="admin-input" id="quantity" name="quantity" type="number" value="{{ old('quantity', $product?->quantity ?? 0) }}" min="0" max="1000000" required>
            @error('quantity') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="low_stock_threshold">Low-stock alert at</label>
            <input class="admin-input" id="low_stock_threshold" name="low_stock_threshold" type="number" value="{{ old('low_stock_threshold', $product?->low_stock_threshold ?? 5) }}" min="0" max="1000000" required>
            @error('low_stock_threshold') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-3 gap-3">
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="rating">Rating</label>
            <input class="admin-input" id="rating" name="rating" type="number" value="{{ old('rating', $product?->rating ?? '4.5') }}" min="0" max="5" step="0.1" required>
            @error('rating') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="priority">Priority</label>
            <input class="admin-input" id="priority" name="priority" type="number" value="{{ old('priority', $product?->priority ?? 50) }}" min="0" max="100" required>
            @error('priority') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="badge">Badge</label>
            <input class="admin-input" id="badge" name="badge" type="text" value="{{ old('badge', $product?->badge ?? 'New') }}" required>
            @error('badge') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="text-sm font-semibold text-zinc-800" for="image">{{ $isEditing ? 'Replace product image' : 'Product image' }}</label>
        <input class="mt-2 block w-full text-sm text-zinc-600 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-3 file:text-sm file:font-semibold file:text-zinc-800 hover:file:bg-zinc-200" id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp">
        @error('image') <p class="admin-error">{{ $message }}</p> @enderror

        @if ($isEditing && $product->image_path)
            <div class="mt-3 flex items-center gap-3 rounded-lg border border-zinc-200 p-3">
                <img class="h-12 w-12 rounded-lg object-cover" src="{{ asset($product->image_path) }}" alt="{{ $product->name }}">
                <p class="text-xs text-zinc-500">Current image remains unless a replacement is selected.</p>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3">
        <label class="flex min-h-12 items-center gap-3 rounded-lg border border-zinc-200 px-3 text-sm text-zinc-700">
            <input class="h-4 w-4 rounded border-zinc-300 text-red-600 focus:ring-red-500" name="is_featured" type="checkbox" value="1" @checked(old('is_featured', $product?->is_featured ?? false))>
            Hero featured
        </label>
        <label class="flex min-h-12 items-center gap-3 rounded-lg border border-zinc-200 px-3 text-sm text-zinc-700">
            <input class="h-4 w-4 rounded border-zinc-300 text-red-600 focus:ring-red-500" name="is_active" type="checkbox" value="1" @checked(old('is_active', $product?->is_active ?? true))>
            Active
        </label>
    </div>

    {{-- Deliverable Locations Section --}}
    @php
        $countries = \App\Support\LocationData::getCountries();
        $indiaStatesWithCities = \App\Support\LocationData::getIndiaStatesWithCities();
        $currentDeliveryMode = old('delivery_mode', $product?->delivery_mode ?? 'all');
        $initialProductLocations = old('deliverable_locations', $product?->deliverable_locations ?? []);
        if (! is_array($initialProductLocations)) {
            $initialProductLocations = [];
        }
    @endphp

    <div class="border-t border-zinc-200 pt-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-emerald-700">Logistics & Shipping</p>
                <h3 class="mt-1 text-base font-semibold text-zinc-950">Deliverable Locations</h3>
                <p class="mt-0.5 text-xs text-zinc-500">Configure whether this product can be delivered everywhere or only to specific regions.</p>
            </div>
            <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-[11px] font-semibold text-zinc-600">Fulfillment</span>
        </div>

        @error('delivery_mode') <p class="admin-error">{{ $message }}</p> @enderror
        @error('deliverable_locations') <p class="admin-error">{{ $message }}</p> @enderror
        @error('deliverable_locations.*') <p class="admin-error">{{ $message }}</p> @enderror

        {{-- Delivery Availability Radio Options --}}
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <label
                id="product-card-mode-all"
                class="relative flex cursor-pointer items-start gap-3 rounded-xl border-2 p-3.5 transition {{ $currentDeliveryMode === 'all' ? 'border-blue-600 bg-blue-50/20' : 'border-zinc-200 hover:border-zinc-300' }}"
            >
                <input
                    type="radio"
                    name="delivery_mode"
                    value="all"
                    id="product-radio-mode-all"
                    class="mt-0.5 h-4 w-4 text-red-600 focus:ring-red-500"
                    @checked($currentDeliveryMode === 'all')
                >
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-zinc-950">Deliver Everywhere</p>
                    <p class="mt-0.5 text-xs text-zinc-500">Ships to all deliverable regions without per-product location restrictions.</p>
                </div>
            </label>

            <label
                id="product-card-mode-specific"
                class="relative flex cursor-pointer items-start gap-3 rounded-xl border-2 p-3.5 transition {{ $currentDeliveryMode === 'specific' || $currentDeliveryMode === 'custom' ? 'border-amber-600 bg-amber-50/20' : 'border-zinc-200 hover:border-zinc-300' }}"
            >
                <input
                    type="radio"
                    name="delivery_mode"
                    value="specific"
                    id="product-radio-mode-specific"
                    class="mt-0.5 h-4 w-4 text-red-600 focus:ring-red-500"
                    @checked($currentDeliveryMode === 'specific' || $currentDeliveryMode === 'custom')
                >
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-zinc-950">Specific Locations</p>
                    <p class="mt-0.5 text-xs text-zinc-500">Restrict orders for this product to designated countries, states, or cities.</p>
                </div>
            </label>
        </div>

        {{-- Specific Locations Configurator Panel --}}
        <div id="product-specific-locations-panel" class="mt-4 space-y-4 {{ $currentDeliveryMode === 'all' ? 'hidden' : '' }}">
            <div class="rounded-xl border border-amber-200/80 bg-gradient-to-br from-amber-50/30 via-white to-amber-50/10 p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-800">Add Deliverable Location Rule</p>

                <div class="mt-3 grid gap-3 sm:grid-cols-3" id="product-location-builder-grid">
                    {{-- Country Selector --}}
                    <div>
                        <label for="product-select-country" class="block text-xs font-bold text-zinc-700">Country <span class="text-red-500">*</span></label>
                        <select
                            id="product-select-country"
                            class="admin-input mt-1 text-xs"
                        >
                            @foreach ($countries as $country)
                                <option value="{{ $country }}" {{ $country === 'India' ? 'selected' : '' }}>{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- State Selector (India only) --}}
                    <div id="product-state-wrapper">
                        <label for="product-select-state" class="block text-xs font-bold text-zinc-700">State / Region</label>
                        <select
                            id="product-select-state"
                            class="admin-input mt-1 text-xs"
                        >
                            <option value="">-- All States (Entire India) --</option>
                            @foreach (array_keys($indiaStatesWithCities) as $state)
                                <option value="{{ $state }}">{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- City Selector (India only) --}}
                    <div id="product-city-wrapper">
                        <label for="product-input-city" class="block text-xs font-bold text-zinc-700">City</label>
                        <input
                            type="text"
                            id="product-input-city"
                            list="product-city-datalist"
                            placeholder="Type or select city (optional)"
                            class="admin-input mt-1 text-xs"
                        />
                        <datalist id="product-city-datalist">
                            {{-- Populated dynamically via JS --}}
                        </datalist>
                    </div>
                </div>

                <div class="mt-2.5 flex flex-wrap items-center justify-between gap-2">
                    <p id="product-country-info-text" class="text-[11px] text-zinc-500">
                        India: Leave state or city empty to deliver to the entire state or whole country.
                    </p>
                    <button
                        type="button"
                        id="product-btn-add-location"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-950 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-zinc-800"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <span>Add Location</span>
                    </button>
                </div>
            </div>

            {{-- Staged Locations Rules Table --}}
            <div class="rounded-xl border border-zinc-200 bg-white">
                <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-2.5">
                    <h4 class="text-xs font-bold text-zinc-950">Active Deliverable Rules (<span id="product-location-count">0</span>)</h4>
                    <button
                        type="button"
                        id="product-btn-clear-locations"
                        class="text-[11px] font-semibold text-red-600 hover:text-red-800 transition"
                    >
                        Clear All
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-zinc-600">
                        <thead class="border-b border-zinc-100 bg-zinc-50/80 font-bold uppercase tracking-wider text-zinc-600 text-[10px]">
                            <tr>
                                <th class="px-3 py-2">#</th>
                                <th class="px-3 py-2">Country</th>
                                <th class="px-3 py-2">State / Region</th>
                                <th class="px-3 py-2">City</th>
                                <th class="px-3 py-2">Scope</th>
                                <th class="px-3 py-2 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody id="product-locations-table-body" class="divide-y divide-zinc-100 font-medium">
                            {{-- Populated by JavaScript --}}
                        </tbody>
                    </table>
                </div>

                <div id="product-locations-empty-state" class="hidden p-6 text-center text-xs text-zinc-500">
                    <p class="font-semibold text-zinc-800">No specific locations added yet</p>
                    <p class="mt-0.5 text-[11px] text-zinc-400">Add at least one country, state, or city rule above, or select "Deliver Everywhere".</p>
                </div>
            </div>

            {{-- Hidden inputs container for form submission --}}
            <div id="product-hidden-locations-inputs"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const initialLocations = @json($initialProductLocations);
            const statesWithCities = @json($indiaStatesWithCities);

            let stagedLocations = Array.isArray(initialLocations) ? [...initialLocations] : [];

            const radioAll = document.getElementById('product-radio-mode-all');
            const radioSpecific = document.getElementById('product-radio-mode-specific');
            const cardAll = document.getElementById('product-card-mode-all');
            const cardSpecific = document.getElementById('product-card-mode-specific');
            const specificPanel = document.getElementById('product-specific-locations-panel');

            const selectCountry = document.getElementById('product-select-country');
            const stateWrapper = document.getElementById('product-state-wrapper');
            const selectState = document.getElementById('product-select-state');
            const cityWrapper = document.getElementById('product-city-wrapper');
            const inputCity = document.getElementById('product-input-city');
            const cityDatalist = document.getElementById('product-city-datalist');
            const infoText = document.getElementById('product-country-info-text');

            const btnAdd = document.getElementById('product-btn-add-location');
            const btnClear = document.getElementById('product-btn-clear-locations');
            const tableBody = document.getElementById('product-locations-table-body');
            const emptyState = document.getElementById('product-locations-empty-state');
            const locationCount = document.getElementById('product-location-count');
            const hiddenInputsContainer = document.getElementById('product-hidden-locations-inputs');

            function updateModeUI() {
                if (!radioSpecific || !radioAll) return;
                const isSpecific = radioSpecific.checked;

                if (isSpecific) {
                    cardSpecific?.classList.add('border-amber-600', 'bg-amber-50/20');
                    cardSpecific?.classList.remove('border-zinc-200');
                    cardAll?.classList.remove('border-blue-600', 'bg-blue-50/20');
                    cardAll?.classList.add('border-zinc-200');
                    specificPanel?.classList.remove('hidden');
                } else {
                    cardAll?.classList.add('border-blue-600', 'bg-blue-50/20');
                    cardAll?.classList.remove('border-zinc-200');
                    cardSpecific?.classList.remove('border-amber-600', 'bg-amber-50/20');
                    cardSpecific?.classList.add('border-zinc-200');
                    specificPanel?.classList.add('hidden');
                }
                syncHiddenInputs();
            }

            function handleCountryChange() {
                if (!selectCountry) return;
                const country = selectCountry.value;
                const isIndia = country.toLowerCase() === 'india';

                if (isIndia) {
                    stateWrapper.style.display = 'block';
                    cityWrapper.style.display = 'block';
                    infoText.textContent = 'India: Leave state or city empty to deliver to the entire state or whole country.';
                } else {
                    stateWrapper.style.display = 'none';
                    cityWrapper.style.display = 'none';
                    infoText.textContent = `${country}: International delivery will cover the entire country.`;
                }
            }

            function handleStateChange() {
                if (!selectState || !cityDatalist || !inputCity) return;
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

            function renderTable() {
                if (!tableBody || !locationCount || !emptyState) return;
                tableBody.innerHTML = '';
                locationCount.textContent = stagedLocations.length;

                if (stagedLocations.length === 0) {
                    emptyState.classList.remove('hidden');
                } else {
                    emptyState.classList.add('hidden');

                    stagedLocations.forEach((loc, index) => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-zinc-50/80 transition';

                        const isIndia = (loc.country || '').toLowerCase() === 'india';
                        let scopeBadge = '';

                        if (!isIndia) {
                            scopeBadge = '<span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-[9px] font-bold text-blue-800">Nationwide (Intl)</span>';
                        } else if (!loc.state) {
                            scopeBadge = '<span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[9px] font-bold text-emerald-800">Entire India</span>';
                        } else if (!loc.city) {
                            scopeBadge = `<span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[9px] font-bold text-amber-800">All ${loc.state}</span>`;
                        } else {
                            scopeBadge = '<span class="inline-flex rounded-full bg-purple-100 px-2 py-0.5 text-[9px] font-bold text-purple-800">City Specific</span>';
                        }

                        tr.innerHTML = `
                            <td class="px-3 py-2 text-zinc-400 font-mono text-[11px]">${index + 1}</td>
                            <td class="px-3 py-2 font-semibold text-zinc-950">${loc.country || '-'}</td>
                            <td class="px-3 py-2 text-zinc-700">${loc.state || '<span class="text-zinc-400 italic">All States</span>'}</td>
                            <td class="px-3 py-2 text-zinc-700">${loc.city || '<span class="text-zinc-400 italic">All Cities</span>'}</td>
                            <td class="px-3 py-2">${scopeBadge}</td>
                            <td class="px-3 py-2 text-right">
                                <button type="button" data-remove-product-loc="${index}" class="rounded p-1 text-zinc-400 hover:bg-red-50 hover:text-red-600 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                </button>
                            </td>
                        `;
                        tableBody.appendChild(tr);
                    });
                }

                syncHiddenInputs();
            }

            function syncHiddenInputs() {
                if (!hiddenInputsContainer) return;
                hiddenInputsContainer.innerHTML = '';
                if (!radioSpecific || !radioSpecific.checked) return;

                stagedLocations.forEach((loc, index) => {
                    const inCountry = document.createElement('input');
                    inCountry.type = 'hidden';
                    inCountry.name = `deliverable_locations[${index}][country]`;
                    inCountry.value = loc.country;
                    hiddenInputsContainer.appendChild(inCountry);

                    if (loc.state) {
                        const inState = document.createElement('input');
                        inState.type = 'hidden';
                        inState.name = `deliverable_locations[${index}][state]`;
                        inState.value = loc.state;
                        hiddenInputsContainer.appendChild(inState);
                    }

                    if (loc.city) {
                        const inCity = document.createElement('input');
                        inCity.type = 'hidden';
                        inCity.name = `deliverable_locations[${index}][city]`;
                        inCity.value = loc.city;
                        hiddenInputsContainer.appendChild(inCity);
                    }
                });
            }

            radioAll?.addEventListener('change', updateModeUI);
            radioSpecific?.addEventListener('change', updateModeUI);
            selectCountry?.addEventListener('change', handleCountryChange);
            selectState?.addEventListener('change', handleStateChange);

            btnAdd?.addEventListener('click', () => {
                if (!selectCountry) return;
                const country = selectCountry.value.trim();
                if (!country) return;

                const isIndia = country.toLowerCase() === 'india';
                const state = isIndia ? (selectState?.value.trim() || null) : null;
                const city = isIndia ? (inputCity?.value.trim() || null) : null;

                // Prevent duplicate
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
                if (inputCity) inputCity.value = '';
                renderTable();
            });

            tableBody?.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-remove-product-loc]');
                if (!btn) return;
                const index = parseInt(btn.getAttribute('data-remove-product-loc'), 10);
                if (!isNaN(index) && index >= 0 && index < stagedLocations.length) {
                    stagedLocations.splice(index, 1);
                    renderTable();
                }
            });

            btnClear?.addEventListener('click', () => {
                if (stagedLocations.length === 0) return;
                if (confirm('Clear all staged deliverable locations for this product?')) {
                    stagedLocations = [];
                    renderTable();
                }
            });

            // Initial setup
            updateModeUI();
            handleCountryChange();
            renderTable();
        });
    </script>

    <button class="inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-red-700 px-5 text-sm font-semibold text-white transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300 focus:ring-offset-2" type="submit">
        {{ $submitLabel }}
    </button>
</form>
