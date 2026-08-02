@php($successMessage = session('status'))

<x-site.layout
    :site="$site"
    page-title="Reset password | FlavourFlow"
    page-description="Choose a new password for your FlavourFlow account."
>
    <section class="relative overflow-hidden bg-brand-surface">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(180,35,24,0.14),_transparent_34%),linear-gradient(180deg,#fff9ed_0%,#ffffff_60%,#fff7ec_100%)]"></div>
        <div class="relative mx-auto flex min-h-[calc(100svh-9rem)] w-full max-w-3xl items-center px-6 py-12 lg:px-8">
            <div class="w-full rounded-[2rem] border border-white/80 bg-white/90 p-6 shadow-[0_24px_70px_rgba(24,24,27,0.12)] backdrop-blur sm:p-8">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-primary">New password</p>
                <h1 class="mt-3 text-3xl font-semibold text-zinc-950">Set a new password.</h1>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-zinc-600">
                    Use the form below to finish resetting your account access.
                </p>

                @if ($successMessage)
                    <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                        {{ $successMessage }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form class="mt-6 space-y-4" method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label class="text-sm font-semibold text-zinc-800" for="email">Email address</label>
                        <input
                            class="mt-2 min-h-12 w-full rounded-2xl border border-zinc-200 bg-white px-4 text-sm outline-none transition focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $request->email) }}"
                            autocomplete="email"
                            required
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-zinc-800" for="password">New password</label>
                            <input
                                class="mt-2 min-h-12 w-full rounded-2xl border border-zinc-200 bg-white px-4 text-sm outline-none transition focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="new-password"
                                required
                            >
                            @error('password')
                                <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-zinc-800" for="password_confirmation">Confirm password</label>
                            <input
                                class="mt-2 min-h-12 w-full rounded-2xl border border-zinc-200 bg-white px-4 text-sm outline-none transition focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                required
                            >
                        </div>
                    </div>

                    <button class="inline-flex min-h-12 w-full items-center justify-center rounded-2xl bg-brand-ink px-5 text-sm font-semibold text-white transition hover:bg-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/20" type="submit">
                        Update password
                    </button>
                </form>

                <div class="mt-6 flex flex-wrap items-center gap-4 text-sm font-semibold">
                    <a class="text-brand-primary transition hover:opacity-80" href="{{ route('login') }}">Back to sign in</a>
                    <a class="text-brand-primary transition hover:opacity-80" href="{{ route('home') }}">Back to shop</a>
                </div>
            </div>
        </div>
    </section>
</x-site.layout>
