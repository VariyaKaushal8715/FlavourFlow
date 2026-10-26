<x-site.layout :site="$site" page-title="My Account | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    @php
        $user = auth()->user();
        $hasProfile = $profile !== null;
    @endphp

    <section class="border-b border-amber-200/60 bg-[radial-gradient(circle_at_top_left,rgba(244,185,66,0.24),transparent_34%),linear-gradient(135deg,#fff9ed_0%,#fff_52%,#fff3df_100%)] py-10 sm:py-14">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <div class="max-w-3xl" data-reveal>
                <p class="text-sm font-semibold text-brand-primary">{{ __('ui.account') }}</p>
                <h1 class="mt-2 text-3xl font-semibold text-zinc-950 sm:text-4xl">{{ __('ui.profile') }}</h1>
                <p class="mt-4 text-base leading-7 text-zinc-600">Keep your contact details and delivery address ready for a smoother checkout.</p>
            </div>
        </div>
    </section>

    <section class="bg-[linear-gradient(180deg,#fff_0%,#fff9ed_48%,#fff_100%)] py-8 sm:py-12 lg:py-16">
        <div class="mx-auto grid w-full max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)] lg:px-8">
            
            {{-- Mobile Account Overview (Visible by default on mobile, hidden on desktop or when editing) --}}
            <div id="mobile-profile-overview" class="{{ $errors->any() ? 'hidden' : 'block' }} space-y-6 lg:hidden" data-reveal>
                @if (session('status'))
                    <div class="rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-amber-50 px-4 py-3.5 text-sm font-medium text-emerald-800 shadow-sm shadow-emerald-950/5">
                        {{ session('status') }}
                    </div>
                @endif

                @php
                    $userFullName = $profile?->full_name ?? $user?->name ?? 'FlavourFlow Customer';
                    $userEmail = $profile?->email ?? $user?->email ?? '';
                    $userMobile = $profile?->mobile_number;
                    $userAddress = $profile?->address;
                    $userLocation = $hasProfile ? collect([$profile->city, $profile->state ?: 'Gujarat', $profile->country, $profile->postal_code])->filter()->join(', ') : null;
                    $avatarLetters = collect(explode(' ', $userFullName))
                        ->filter()
                        ->take(2)
                        ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                        ->join('');
                @endphp

                {{-- Compact Profile Overview Card --}}
                <div class="overflow-hidden rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_20px_50px_rgba(120,53,15,0.08)] ring-1 ring-white">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-red-600 via-amber-600 to-amber-500 text-lg font-bold text-white shadow-md ring-4 ring-amber-100">
                            {{ $avatarLetters ?: 'U' }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <h2 class="truncate text-xl font-bold text-zinc-950">{{ $userFullName }}</h2>
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            </div>
                            <p class="truncate text-sm text-zinc-500" data-profile-contact-value="email">{{ $userEmail }}</p>
                        </div>
                    </div>

                    {{-- Summary Fields --}}
                    <div class="mt-6 rounded-2xl border border-amber-100 bg-amber-50/40 divide-y divide-amber-100/70">
                        {{-- Mobile number --}}
                        <div class="flex items-start justify-between gap-4 p-3.5 text-sm">
                            <span class="flex items-center gap-2 font-medium text-zinc-500">
                                <svg class="h-4 w-4 text-brand-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                </svg>
                                {{ __('ui.mobile_number') }}
                            </span>
                            <span class="text-right font-semibold text-zinc-900" data-profile-contact-value="mobile_number">
                                {{ $userMobile ?: 'Not provided yet' }}
                            </span>
                        </div>

                        {{-- Delivery Address --}}
                        <div class="flex items-start justify-between gap-4 p-3.5 text-sm">
                            <span class="flex items-center gap-2 font-medium text-zinc-500">
                                <svg class="h-4 w-4 text-brand-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                                {{ __('ui.address_line') }}
                            </span>
                            <span class="max-w-[13rem] text-right font-semibold text-zinc-900">
                                @if ($userAddress)
                                    {{ $userAddress }}
                                @else
                                    <span class="italic text-zinc-400 font-normal">No address saved</span>
                                @endif
                            </span>
                        </div>

                        {{-- Location & Postal Code --}}
                        <div class="flex items-start justify-between gap-4 p-3.5 text-sm">
                            <span class="flex items-center gap-2 font-medium text-zinc-500">
                                <svg class="h-4 w-4 text-brand-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.75a1.5 1.5 0 011.5-1.5h3a1.5 1.5 0 011.5 1.5V21" />
                                </svg>
                                Location & PIN
                            </span>
                            <span class="text-right font-semibold text-zinc-900">
                                @if ($userLocation)
                                    {{ $userLocation }}
                                @else
                                    <span class="italic text-zinc-400 font-normal">Not specified</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Prominent "Edit Personal Details" Button --}}
                    <button
                        type="button"
                        id="open-mobile-profile-edit"
                        class="mt-6 w-full inline-flex items-center justify-center gap-2.5 rounded-2xl bg-gradient-to-r from-red-700 via-amber-600 to-amber-500 px-5 py-3.5 text-sm font-semibold text-white shadow-[0_16px_34px_rgba(180,35,24,0.25)] transition hover:brightness-110 active:scale-[0.99] focus:outline-none focus-visible:ring-4 focus-visible:ring-amber-300"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        <span>Edit Personal Details</span>
                    </button>
                </div>

                {{-- Quick Links on Mobile --}}
                <div class="grid grid-cols-2 gap-3.5">
                    {{-- My Coupons --}}
                    <a
                        href="{{ route('account.coupons') }}"
                        class="flex flex-col gap-2 rounded-2xl border border-amber-200/70 bg-white/95 p-4 shadow-sm transition hover:border-amber-300 active:scale-[0.98]"
                    >
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-amber-200/80 bg-gradient-to-br from-amber-50 to-red-50 text-brand-primary">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs font-bold text-zinc-950">My Coupons</p>
                            <p class="text-[11px] text-zinc-500">Savings & vouchers</p>
                        </div>
                    </a>

                    {{-- My Orders --}}
                    <a
                        href="{{ route('account.orders') }}"
                        class="flex flex-col gap-2 rounded-2xl border border-amber-200/70 bg-white/95 p-4 shadow-sm transition hover:border-amber-300 active:scale-[0.98]"
                    >
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-amber-200/80 bg-gradient-to-br from-amber-50 to-red-50 text-brand-primary">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs font-bold text-zinc-950">My Orders</p>
                            <p class="text-[11px] text-zinc-500">Track shipments</p>
                        </div>
                    </a>

                    {{-- Wishlist --}}
                    <a
                        href="{{ route('wishlist.index') }}"
                        class="flex flex-col gap-2 rounded-2xl border border-amber-200/70 bg-white/95 p-4 shadow-sm transition hover:border-amber-300 active:scale-[0.98]"
                    >
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-amber-200/80 bg-gradient-to-br from-amber-50 to-red-50 text-brand-primary">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m12 21-1.45-1.32C5.4 15 2 11.92 2 8.15 2 5.07 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.07 22 8.15c0 3.77-3.4 6.85-8.55 11.54L12 21Z"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs font-bold text-zinc-950">{{ __('ui.wishlist') }}</p>
                            <p class="text-[11px] text-zinc-500">Saved items</p>
                        </div>
                    </a>

                    {{-- My Cart --}}
                    <a
                        href="{{ route('cart.index') }}"
                        class="flex flex-col gap-2 rounded-2xl border border-amber-200/70 bg-white/95 p-4 shadow-sm transition hover:border-amber-300 active:scale-[0.98]"
                    >
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-amber-200/80 bg-gradient-to-br from-amber-50 to-red-50 text-brand-primary">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M6 5v1H4.667a1.75 1.75 0 00-1.743 1.598l-.826 9.087A1.75 1.75 0 003.84 19h12.32a1.75 1.75 0 001.743-1.815l-.826-9.087A1.75 1.75 0 0015.333 6H14V5a4 4 0 00-8 0zm4-2.5A2.5 2.5 0 007.5 5v1h5V5A2.5 2.5 0 0010 2.5zM7.5 11a.75.75 0 01.75-.75h4a.75.75 0 010 1.5h-4a.75.75 0 01-.75-.75z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs font-bold text-zinc-950">{{ __('ui.cart') }}</p>
                            <p class="text-[11px] text-zinc-500">Your bag</p>
                        </div>
                    </a>
                </div>

                {{-- Privacy & Security link on mobile overview --}}
                <div class="rounded-2xl border border-amber-100 bg-amber-50/30 p-4 flex items-center justify-between gap-3 text-xs">
                    <div>
                        <p class="font-semibold text-zinc-900">Privacy & Cookie Preferences</p>
                        <p class="text-zinc-500 text-[11px]">Manage essential and optional tracking consent</p>
                    </div>
                    <button
                        type="button"
                        id="open-privacy-mobile"
                        class="shrink-0 rounded-xl border border-amber-300/80 bg-white px-3 py-1.5 font-semibold text-brand-primary transition hover:bg-amber-50"
                    >
                        Manage
                    </button>
                </div>
            </div>

            {{-- Main Editable Profile Form Card (Always visible on desktop lg:block; on mobile shown when tapped or on validation errors) --}}
            <div id="profile-form-container" class="{{ $errors->any() ? 'block' : 'hidden' }} lg:block rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.10)] ring-1 ring-white sm:p-8" data-reveal>
                @if (session('status'))
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-amber-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-sm shadow-emerald-950/5">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Mobile Header with Cancel Action --}}
                <div class="mb-6 flex items-center justify-between border-b border-amber-200/70 pb-4 lg:hidden">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-brand-primary">Account Details</p>
                        <h2 class="text-lg font-bold text-zinc-950">Edit Personal Details</h2>
                    </div>
                    <button
                        type="button"
                        class="mobile-profile-cancel inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-950 active:scale-95"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Cancel</span>
                    </button>
                </div>

                {{-- Desktop Header --}}
                <div class="hidden flex-wrap items-start justify-between gap-4 lg:flex">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-brand-primary">{{ $hasProfile ? __('ui.update_profile') : __('ui.save_details') }}</p>
                        <h2 class="mt-2 text-2xl font-semibold text-zinc-950">{{ $hasProfile ? __('ui.update_profile') : __('ui.save_details') }}</h2>
                    </div>
                    <span class="rounded-full border border-amber-300/60 bg-gradient-to-r from-amber-100 to-red-50 px-3 py-1 text-xs font-semibold text-brand-primary shadow-sm shadow-amber-900/5">Secure account</span>
                </div>

                <form class="mt-6 sm:mt-8 space-y-5" method="POST" action="{{ $hasProfile ? route('account.profile.update') : route('account.profile.store') }}">
                    @csrf
                    @if ($hasProfile)
                        @method('PUT')
                    @endif

                    <div class="grid gap-5 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-semibold text-zinc-800">{{ __('ui.full_name') }}</span>
                            <input
                                class="mt-2 w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15"
                                type="text"
                                name="full_name"
                                value="{{ old('full_name', $profile?->full_name ?? $user?->name) }}"
                                autocomplete="name"
                                required
                            >
                            @error('full_name')
                                <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-zinc-800">{{ __('ui.mobile_number') }}</span>
                            <div class="mt-2 flex flex-col gap-3 sm:flex-row" data-profile-contact>
                                <input
                                    class="w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15"
                                    type="tel"
                                    name="mobile_number"
                                    value="{{ old('mobile_number', $profile?->mobile_number) }}"
                                    autocomplete="tel"
                                    pattern="[0-9+\-\s()]{7,25}"
                                    required
                                    data-profile-contact-input="mobile_number"
                                >
                                @if ($hasProfile)
                                    <button
                                        class="inline-flex min-h-12 shrink-0 items-center justify-center rounded-2xl border border-amber-300/70 bg-gradient-to-r from-amber-100 to-red-50 px-4 text-sm font-semibold text-brand-primary shadow-sm transition hover:-translate-y-0.5 hover:border-brand-primary/40 hover:bg-brand-surface focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary/30 disabled:cursor-not-allowed disabled:opacity-70"
                                        type="button"
                                        data-profile-contact-button
                                        data-profile-contact-url="{{ route('account.profile.mobile_number.update') }}"
                                        data-profile-contact-field="mobile_number"
                                        data-profile-contact-loading="Updating..."
                                    >
                                        {{ __('ui.update_profile') }}
                                    </button>
                                @endif
                            </div>
                            @if ($hasProfile)
                                <p class="mt-2 min-h-5 text-xs font-semibold" data-profile-contact-message="mobile_number" role="status" aria-live="polite"></p>
                            @endif
                            @error('mobile_number')
                                <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </label>
                    </div>

                    <label class="block">
                        <span class="text-sm font-semibold text-zinc-800">{{ __('ui.email_address') }}</span>
                        <div class="mt-2 flex flex-col gap-3 sm:flex-row" data-profile-contact>
                            <input
                                class="w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15"
                                type="email"
                                name="email"
                                value="{{ old('email', $profile?->email ?? $user?->email) }}"
                                autocomplete="email"
                                required
                                data-profile-contact-input="email"
                            >
                            @if ($hasProfile)
                                <button
                                    class="inline-flex min-h-12 shrink-0 items-center justify-center rounded-2xl border border-amber-300/70 bg-gradient-to-r from-amber-100 to-red-50 px-4 text-sm font-semibold text-brand-primary shadow-sm transition hover:-translate-y-0.5 hover:border-brand-primary/40 hover:bg-brand-surface focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary/30 disabled:cursor-not-allowed disabled:opacity-70"
                                    type="button"
                                    data-profile-contact-button
                                    data-profile-contact-url="{{ route('account.profile.email.update') }}"
                                    data-profile-contact-field="email"
                                    data-profile-contact-loading="Updating..."
                                >
                                    {{ __('ui.update_profile') }}
                                </button>
                            @endif
                        </div>
                        @if ($hasProfile)
                            <p class="mt-2 min-h-5 text-xs font-semibold" data-profile-contact-message="email" role="status" aria-live="polite"></p>
                        @endif
                        @error('email')
                            <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                        @enderror
                    </label>

                    <label class="block">
                        <span class="text-sm font-semibold text-zinc-800">{{ __('ui.address_line') }}</span>
                        <textarea class="mt-2 min-h-28 sm:min-h-32 w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15" name="address" autocomplete="street-address" required>{{ old('address', $profile?->address) }}</textarea>
                        @error('address')
                            <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                        @enderror
                    </label>

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <label class="block">
                            <span class="text-sm font-semibold text-zinc-800">{{ __('ui.city') }}</span>
                            <input
                                class="mt-2 w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15"
                                type="text"
                                name="city"
                                value="{{ old('city', $profile?->city) }}"
                                autocomplete="address-level2"
                                required
                            >
                            @error('city')
                                <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-zinc-800">{{ __('ui.state') }}</span>
                            <input
                                class="mt-2 w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15"
                                type="text"
                                name="state"
                                value="{{ old('state', $profile?->state ?: 'Gujarat') }}"
                                autocomplete="address-level1"
                                readonly
                                required
                            >
                            @error('state')
                                <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-zinc-800">Country</span>
                            <input
                                class="mt-2 w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15"
                                type="text"
                                name="country"
                                value="{{ old('country', $profile?->country) }}"
                                autocomplete="country-name"
                                required
                            >
                            @error('country')
                                <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-zinc-800">{{ __('ui.pincode') }}</span>
                            <input
                                class="mt-2 w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15"
                                type="text"
                                name="postal_code"
                                value="{{ old('postal_code', $profile?->postal_code) }}"
                                autocomplete="postal-code"
                                required
                            >
                            @error('postal_code')
                                <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </label>
                    </div>

                    {{-- Form Actions: Save Changes & Cancel --}}
                    <div class="flex flex-wrap items-center gap-3 pt-3">
                        <button class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-red-700 to-amber-500 px-6 py-3.5 text-sm font-semibold text-white shadow-[0_16px_34px_rgba(180,35,24,0.24)] transition hover:-translate-y-0.5 hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300 active:scale-[0.98]" type="submit">
                            {{ $hasProfile ? __('ui.update_profile') : __('ui.save_details') }}
                        </button>

                        <button
                            type="button"
                            class="mobile-profile-cancel flex-1 sm:flex-none inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3.5 text-sm font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50 hover:text-zinc-950 active:scale-[0.98] lg:hidden"
                        >
                            Cancel
                        </button>

                        <a class="hidden sm:inline-flex items-center rounded-2xl border border-amber-200 bg-white px-5 py-3.5 text-sm font-semibold text-zinc-700 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-primary/40 hover:bg-brand-surface hover:text-zinc-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary/30" href="{{ route('home') }}">
                            {{ __('ui.home') }}
                        </a>
                    </div>
                </form>

                @if ($hasProfile)
                    <div class="mt-8 border-t border-amber-200/70 pt-6">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-zinc-950">{{ __('ui.delete_saved_details') }}</h3>
                                <p class="mt-1 text-sm leading-6 text-zinc-500">Remove your profile and address details from this account.</p>
                            </div>
                            <form method="POST" action="{{ route('account.profile.destroy') }}" onsubmit="return confirm('Delete your profile and address details?');">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex items-center rounded-2xl border border-red-200 bg-gradient-to-r from-red-50 to-amber-50 px-4 py-3 text-sm font-semibold text-red-700 shadow-sm transition hover:-translate-y-0.5 hover:border-red-300 hover:bg-red-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-300" type="submit">
                                    {{ __('ui.delete_saved_details') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <div class="mt-8 border-t border-amber-200/70 pt-6" id="privacy-cookies">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-primary">Privacy & Cookies</p>
                            <h3 class="mt-1 text-lg font-semibold text-zinc-950">Manage your privacy preference</h3>
                            <p class="mt-1 max-w-xl text-sm leading-6 text-zinc-500">Essential cookies remain enabled for account, cart, and order functionality. Optional activity tracking can be changed at any time.</p>
                        </div>
                        <a class="inline-flex items-center rounded-xl border border-amber-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-700 transition hover:border-brand-primary hover:text-brand-primary focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/15" href="{{ route('privacy-policy') }}">Read Privacy Policy</a>
                    </div>

                    <div class="mt-5 flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-zinc-900">Optional activity tracking</p>
                            <p class="mt-1 text-xs text-zinc-500">Current status: <span class="font-semibold {{ $privacyConsent?->allowsActivityTracking() ? 'text-emerald-700' : 'text-zinc-700' }}">{{ $privacyConsent?->allowsActivityTracking() ? 'Allowed' : 'Declined' }}</span></p>
                        </div>
                        <form method="POST" action="{{ route('privacy-consent.update') }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="consent_status" value="{{ $privacyConsent?->allowsActivityTracking() ? 'declined' : 'allowed' }}">
                            <button class="inline-flex items-center rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-primary focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20" type="submit">{{ $privacyConsent?->allowsActivityTracking() ? 'Revoke Optional Tracking' : 'Allow Optional Tracking' }}</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Desktop Aside (Hidden on mobile because mobile overview has compact layout) --}}
            <aside class="hidden space-y-5 lg:block" data-reveal>
                {{-- Quick links: Wishlist, Cart, Coupons, Orders --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- My Coupons card --}}
                    <a
                        href="{{ route('account.coupons') }}"
                        class="group flex flex-col gap-3 rounded-3xl border border-amber-200/70 bg-white/95 p-5 shadow-[0_8px_30px_rgba(120,53,15,0.08)] transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-[0_12px_40px_rgba(120,53,15,0.14)]"
                    >
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50 to-red-50 text-brand-primary shadow-sm transition group-hover:border-brand-primary/30 group-hover:shadow-md">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-zinc-950">My Coupons</p>
                            <p class="mt-0.5 text-xs text-zinc-500">Savings & vouchers</p>
                        </div>
                        <span class="mt-auto text-xs font-semibold text-brand-primary transition group-hover:translate-x-0.5">View &rarr;</span>
                    </a>

                    {{-- My Orders card --}}
                    <a
                        href="{{ route('account.orders') }}"
                        class="group flex flex-col gap-3 rounded-3xl border border-amber-200/70 bg-white/95 p-5 shadow-[0_8px_30px_rgba(120,53,15,0.08)] transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-[0_12px_40px_rgba(120,53,15,0.14)]"
                    >
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50 to-red-50 text-brand-primary shadow-sm transition group-hover:border-brand-primary/30 group-hover:shadow-md">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-zinc-950">My Orders</p>
                            <p class="mt-0.5 text-xs text-zinc-500">Track shipments</p>
                        </div>
                        <span class="mt-auto text-xs font-semibold text-brand-primary transition group-hover:translate-x-0.5">View &rarr;</span>
                    </a>

                    {{-- Wishlist card --}}
                    <a
                        href="{{ route('wishlist.index') }}"
                        class="group flex flex-col gap-3 rounded-3xl border border-amber-200/70 bg-white/95 p-5 shadow-[0_8px_30px_rgba(120,53,15,0.08)] transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-[0_12px_40px_rgba(120,53,15,0.14)]"
                    >
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50 to-red-50 text-brand-primary shadow-sm transition group-hover:border-brand-primary/30 group-hover:shadow-md">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m12 21-1.45-1.32C5.4 15 2 11.92 2 8.15 2 5.07 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.07 22 8.15c0 3.77-3.4 6.85-8.55 11.54L12 21Z"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-zinc-950">{{ __('ui.wishlist') }}</p>
                            <p class="mt-0.5 text-xs text-zinc-500">Saved items</p>
                        </div>
                        <span class="mt-auto text-xs font-semibold text-brand-primary transition group-hover:translate-x-0.5">View &rarr;</span>
                    </a>

                    {{-- My Cart card --}}
                    <a
                        href="{{ route('cart.index') }}"
                        class="group flex flex-col gap-3 rounded-3xl border border-amber-200/70 bg-white/95 p-5 shadow-[0_8px_30px_rgba(120,53,15,0.08)] transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-[0_12px_40px_rgba(120,53,15,0.14)]"
                    >
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50 to-red-50 text-brand-primary shadow-sm transition group-hover:border-brand-primary/30 group-hover:shadow-md">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M6 5v1H4.667a1.75 1.75 0 00-1.743 1.598l-.826 9.087A1.75 1.75 0 003.84 19h12.32a1.75 1.75 0 001.743-1.815l-.826-9.087A1.75 1.75 0 0015.333 6H14V5a4 4 0 00-8 0zm4-2.5A2.5 2.5 0 007.5 5v1h5V5A2.5 2.5 0 0010 2.5zM7.5 11a.75.75 0 01.75-.75h4a.75.75 0 010 1.5h-4a.75.75 0 01-.75-.75z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-zinc-950">{{ __('ui.cart') }}</p>
                            <p class="mt-0.5 text-xs text-zinc-500">Your bag</p>
                        </div>
                        <span class="mt-auto text-xs font-semibold text-brand-primary transition group-hover:translate-x-0.5">View &rarr;</span>
                    </a>
                </div>

                {{-- Profile Snapshot (Desktop) --}}
                <div class="overflow-hidden rounded-3xl border border-white/10 bg-[radial-gradient(circle_at_top_left,rgba(180,35,24,0.38),transparent_32%),radial-gradient(circle_at_bottom_right,rgba(244,185,66,0.22),transparent_28%),linear-gradient(145deg,#09090b_0%,#18181b_100%)] text-white shadow-[0_28px_80px_rgba(9,9,11,0.28)]">
                    <div class="border-b border-white/10 px-6 py-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-brand-accent">Profile snapshot</p>
                        <h2 class="mt-2 text-xl font-semibold">Your saved identity</h2>
                    </div>

                    <dl class="divide-y divide-white/10">
                        <div class="flex items-start justify-between gap-6 px-6 py-4">
                            <dt class="text-sm text-white/55">{{ __('ui.full_name') }}</dt>
                            <dd class="text-right text-sm font-semibold text-white">{{ $profile?->full_name ?? $user?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-6 px-6 py-4">
                            <dt class="text-sm text-white/55">{{ __('ui.mobile_number') }}</dt>
                            <dd class="text-right text-sm font-semibold text-white" data-profile-contact-value="mobile_number">{{ $profile?->mobile_number ?? '-' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-6 px-6 py-4">
                            <dt class="text-sm text-white/55">{{ __('ui.email_address') }}</dt>
                            <dd class="text-right text-sm font-semibold text-white" data-profile-contact-value="email">{{ $profile?->email ?? $user?->email ?? '-' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-6 px-6 py-4">
                            <dt class="text-sm text-white/55">{{ __('ui.address_line') }}</dt>
                            <dd class="max-w-[14rem] text-right text-sm font-semibold text-white">{{ $profile?->address ?? '-' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-6 px-6 py-4">
                            <dt class="text-sm text-white/55">Location</dt>
                            <dd class="text-right text-sm font-semibold text-white">
                                @if ($hasProfile)
                                    {{ collect([$profile->city, $profile->state, $profile->country])->filter()->join(', ') }}
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-6 px-6 py-4">
                            <dt class="text-sm text-white/55">{{ __('ui.pincode') }}</dt>
                            <dd class="text-right text-sm font-semibold text-white">{{ $profile?->postal_code ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </aside>
        </div>
    </section>

    {{-- Mobile Profile Toggle Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const openEditBtn = document.getElementById('open-mobile-profile-edit');
            const openPrivacyBtn = document.getElementById('open-privacy-mobile');
            const overviewEl = document.getElementById('mobile-profile-overview');
            const formContainerEl = document.getElementById('profile-form-container');
            const cancelBtns = document.querySelectorAll('.mobile-profile-cancel');

            if (openEditBtn && overviewEl && formContainerEl) {
                openEditBtn.addEventListener('click', () => {
                    overviewEl.classList.add('hidden');
                    overviewEl.classList.remove('block');
                    formContainerEl.classList.remove('hidden');
                    formContainerEl.classList.add('block');
                    formContainerEl.scrollIntoView({ behavior: 'smooth', block: 'start' });

                    const firstInput = formContainerEl.querySelector('input[name="full_name"]');
                    if (firstInput) {
                        firstInput.focus();
                    }
                });
            }

            if (openPrivacyBtn && overviewEl && formContainerEl) {
                openPrivacyBtn.addEventListener('click', () => {
                    overviewEl.classList.add('hidden');
                    overviewEl.classList.remove('block');
                    formContainerEl.classList.remove('hidden');
                    formContainerEl.classList.add('block');
                    const privacyEl = document.getElementById('privacy-cookies');
                    if (privacyEl) {
                        privacyEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            }

            cancelBtns.forEach((btn) => {
                btn.addEventListener('click', () => {
                    if (overviewEl && formContainerEl) {
                        formContainerEl.classList.add('hidden');
                        formContainerEl.classList.remove('block');
                        overviewEl.classList.remove('hidden');
                        overviewEl.classList.add('block');
                        overviewEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        });
    </script>
</x-site.layout>

