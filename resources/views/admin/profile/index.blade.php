<x-admin.layout title="Admin Profile">
    <main class="mx-auto w-full max-w-5xl px-4 py-8 sm:px-6 lg:px-8" x-data="{ submitting: false }">

        {{-- Page Header --}}
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-200 pb-6">
            <div>
                <nav class="flex items-center gap-2 text-xs font-medium text-zinc-500">
                    <a href="{{ route('admin.index') }}" class="transition hover:text-zinc-900">Dashboard</a>
                    <svg class="h-3 w-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                    <span class="text-zinc-950 font-semibold">Settings</span>
                </nav>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">Admin Profile</h1>
                <p class="mt-1 text-sm text-zinc-500">Update your administrator account details, contact information, and address.</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                    Admin Active
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700">
                    #ADM-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}
                </span>
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
                    <span>Please correct the errors below:</span>
                </div>
                <ul class="mt-2 list-inside list-disc text-xs text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-6" @submit="submitting = true">
            @csrf
            @method('PUT')

            {{-- SECTION 1: Personal & Account Details --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="border-b border-zinc-100 pb-5">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-800">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        </span>
                        <h2 class="text-base font-bold text-zinc-950">Personal Details</h2>
                    </div>
                    <p class="mt-1 text-xs text-zinc-500">Your official administrator name and contact information.</p>
                </div>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    {{-- Full Name --}}
                    <div>
                        <label for="full_name" class="block text-xs font-semibold text-zinc-800">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            value="{{ old('full_name', $profile->full_name ?: $user->name) }}"
                            required
                            placeholder="e.g. Kaushal Variya"
                            class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-950 focus:outline-none focus:ring-2 focus:ring-zinc-950/10 transition"
                        >
                        @error('full_name')
                            <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email Address --}}
                    <div>
                        <label for="email" class="block text-xs font-semibold text-zinc-800">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $profile->email ?: $user->email) }}"
                            required
                            placeholder="admin@flavourflow.com"
                            class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-950 focus:outline-none focus:ring-2 focus:ring-zinc-950/10 transition"
                        >
                        @error('email')
                            <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Mobile Number --}}
                    <div>
                        <label for="mobile_number" class="block text-xs font-semibold text-zinc-800">
                            Mobile Number
                        </label>
                        <input
                            type="tel"
                            id="mobile_number"
                            name="mobile_number"
                            value="{{ old('mobile_number', $profile->mobile_number) }}"
                            placeholder="+91 98765 43210"
                            class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-950 focus:outline-none focus:ring-2 focus:ring-zinc-950/10 transition"
                        >
                        @error('mobile_number')
                            <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Date of Birth --}}
                    <div>
                        <label for="date_of_birth" class="block text-xs font-semibold text-zinc-800">
                            Date of Birth
                        </label>
                        <input
                            type="date"
                            id="date_of_birth"
                            name="date_of_birth"
                            max="{{ date('Y-m-d') }}"
                            value="{{ old('date_of_birth', $profile->date_of_birth ? $profile->date_of_birth->format('Y-m-d') : '') }}"
                            class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-950 focus:outline-none focus:ring-2 focus:ring-zinc-950/10 transition"
                        >
                        @error('date_of_birth')
                            <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Gender --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-zinc-800">
                            Gender
                        </label>
                        @php
                            $selectedGender = old('gender', $profile->gender);
                        @endphp
                        <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-zinc-50/50 p-3 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-100 has-checked:border-zinc-950 has-checked:bg-zinc-950 has-checked:text-white">
                                <input type="radio" name="gender" value="male" @checked($selectedGender === 'male') class="sr-only">
                                <span>Male</span>
                            </label>

                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-zinc-50/50 p-3 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-100 has-checked:border-zinc-950 has-checked:bg-zinc-950 has-checked:text-white">
                                <input type="radio" name="gender" value="female" @checked($selectedGender === 'female') class="sr-only">
                                <span>Female</span>
                            </label>

                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-zinc-50/50 p-3 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-100 has-checked:border-zinc-950 has-checked:bg-zinc-950 has-checked:text-white">
                                <input type="radio" name="gender" value="other" @checked($selectedGender === 'other') class="sr-only">
                                <span>Other</span>
                            </label>

                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-zinc-50/50 p-3 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-100 has-checked:border-zinc-950 has-checked:bg-zinc-950 has-checked:text-white">
                                <input type="radio" name="gender" value="prefer_not_to_say" @checked($selectedGender === 'prefer_not_to_say' || blank($selectedGender)) class="sr-only">
                                <span>Prefer not to say</span>
                            </label>
                        </div>
                        @error('gender')
                            <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- SECTION 2: Address & Location --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="border-b border-zinc-100 pb-5">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-800">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                        </span>
                        <h2 class="text-base font-bold text-zinc-950">Address & Location</h2>
                    </div>
                    <p class="mt-1 text-xs text-zinc-500">Official administrative and regional address information.</p>
                </div>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    {{-- Street Address --}}
                    <div class="sm:col-span-2">
                        <label for="address" class="block text-xs font-semibold text-zinc-800">
                            Street Address
                        </label>
                        <textarea
                            id="address"
                            name="address"
                            rows="2"
                            placeholder="e.g. 42 Corporate Avenue, Business Park"
                            class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-950 focus:outline-none focus:ring-2 focus:ring-zinc-950/10 transition"
                        >{{ old('address', $profile->address) }}</textarea>
                        @error('address')
                            <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- City --}}
                    <div>
                        <label for="city" class="block text-xs font-semibold text-zinc-800">
                            City
                        </label>
                        <input
                            type="text"
                            id="city"
                            name="city"
                            value="{{ old('city', $profile->city) }}"
                            placeholder="e.g. Ahmedabad"
                            class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-950 focus:outline-none focus:ring-2 focus:ring-zinc-950/10 transition"
                        >
                        @error('city')
                            <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- State --}}
                    <div>
                        <label for="state" class="block text-xs font-semibold text-zinc-800">
                            State / Province
                        </label>
                        <input
                            type="text"
                            id="state"
                            name="state"
                            value="{{ old('state', $profile->state) }}"
                            placeholder="e.g. Gujarat"
                            class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-950 focus:outline-none focus:ring-2 focus:ring-zinc-950/10 transition"
                        >
                        @error('state')
                            <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Country --}}
                    <div>
                        <label for="country" class="block text-xs font-semibold text-zinc-800">
                            Country
                        </label>
                        <input
                            type="text"
                            id="country"
                            name="country"
                            value="{{ old('country', $profile->country ?: 'India') }}"
                            placeholder="e.g. India"
                            class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-950 focus:outline-none focus:ring-2 focus:ring-zinc-950/10 transition"
                        >
                        @error('country')
                            <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- PIN / ZIP Code --}}
                    <div>
                        <label for="postal_code" class="block text-xs font-semibold text-zinc-800">
                            PIN / ZIP Code
                        </label>
                        <input
                            type="text"
                            id="postal_code"
                            name="postal_code"
                            value="{{ old('postal_code', $profile->postal_code) }}"
                            placeholder="e.g. 380001"
                            class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-950 focus:outline-none focus:ring-2 focus:ring-zinc-950/10 transition"
                        >
                        @error('postal_code')
                            <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end rounded-2xl border border-zinc-200 bg-white p-4 sm:p-5 shadow-sm">
                <a
                    href="{{ route('admin.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-5 py-2.5 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50 active:scale-95 text-center"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    :disabled="submitting"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-950 px-6 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-zinc-950 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70 active:scale-95"
                >
                    <svg x-show="submitting" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg x-show="!submitting" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span x-text="submitting ? 'Saving Profile...' : 'Update Profile'">Update Profile</span>
                </button>
            </div>
        </form>

    </main>
</x-admin.layout>
