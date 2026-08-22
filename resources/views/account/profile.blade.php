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

    <section class="bg-[linear-gradient(180deg,#fff_0%,#fff9ed_48%,#fff_100%)] py-12 sm:py-16">
        <div class="mx-auto grid w-full max-w-7xl gap-8 px-6 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)] lg:px-8">
            <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.10)] ring-1 ring-white sm:p-8" data-reveal>
                @if (session('status'))
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-amber-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-sm shadow-emerald-950/5">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-brand-primary">{{ $hasProfile ? __('ui.update_profile') : __('ui.save_details') }}</p>
                        <h2 class="mt-2 text-2xl font-semibold text-zinc-950">{{ $hasProfile ? __('ui.update_profile') : __('ui.save_details') }}</h2>
                    </div>
                    <span class="rounded-full border border-amber-300/60 bg-gradient-to-r from-amber-100 to-red-50 px-3 py-1 text-xs font-semibold text-brand-primary shadow-sm shadow-amber-900/5">Secure account</span>
                </div>

                <form class="mt-8 space-y-5" method="POST" action="{{ $hasProfile ? route('account.profile.update') : route('account.profile.store') }}">
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
                        <textarea class="mt-2 min-h-32 w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15" name="address" autocomplete="street-address" required>{{ old('address', $profile?->address) }}</textarea>
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
                                value="{{ old('state', $profile?->state) }}"
                                autocomplete="address-level1"
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

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button class="inline-flex items-center rounded-2xl bg-gradient-to-r from-red-700 to-amber-500 px-5 py-3 text-sm font-semibold text-white shadow-[0_16px_34px_rgba(180,35,24,0.24)] transition hover:-translate-y-0.5 hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300" type="submit">
                            {{ $hasProfile ? __('ui.update_profile') : __('ui.save_details') }}
                        </button>
                        <a class="inline-flex items-center rounded-2xl border border-amber-200 bg-white px-5 py-3 text-sm font-semibold text-zinc-700 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-primary/40 hover:bg-brand-surface hover:text-zinc-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary/30" href="{{ route('home') }}">
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
            </div>

            <aside class="space-y-5" data-reveal>
                {{-- Quick links: Wishlist & Cart --}}
                <div class="grid grid-cols-2 gap-4">
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
</x-site.layout>
