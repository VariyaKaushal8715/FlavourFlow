@php
    $isRegister = ($activeForm ?? 'login') === 'register';
    $successMessage = session('success') ?? session('status');
@endphp

<x-site.layout
    :site="$site"
    :show-footer="false"
    page-title="Sign in or create an account | FlavourFlow"
    page-description="Sign in to your FlavourFlow account or create a new one to save your wishlist and speed up checkout."
>
    <section class="relative overflow-hidden bg-brand-surface">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(180,35,24,0.14),_transparent_34%),radial-gradient(circle_at_85%_15%,_rgba(244,185,66,0.22),_transparent_24%),linear-gradient(180deg,#fff9ed_0%,#ffffff_55%,#fff7ec_100%)]"></div>
        <div class="absolute left-[-10rem] top-24 h-72 w-72 rounded-full bg-brand-primary/10 blur-3xl"></div>
        <div class="absolute bottom-0 right-[-8rem] h-80 w-80 rounded-full bg-brand-accent/25 blur-3xl"></div>

        <div class="relative mx-auto grid min-h-[calc(100svh-9rem)] w-full max-w-7xl items-center gap-12 px-6 py-10 lg:grid-cols-[0.95fr_1.05fr] lg:px-8">
            <div class="max-w-2xl text-brand-ink">
                <div class="inline-flex items-center gap-3 rounded-full border border-brand-primary/15 bg-white/80 px-4 py-2 text-sm font-semibold text-brand-primary shadow-sm backdrop-blur">
                    <img class="h-8 w-8 rounded-lg object-cover" src="{{ asset($site['brand']['logo']) }}" alt="{{ $site['brand']['name'] }} mark">
                    {{ $site['brand']['tagline'] }}
                </div>

                <h1 class="mt-8 text-4xl font-semibold leading-tight sm:text-5xl">
                    {{ __('ui.hero_subtitle') }}
                </h1>
                <p class="mt-5 max-w-xl text-base leading-8 text-zinc-600 sm:text-lg">
                    {{ __('ui.hero_description') }}
                </p>
            </div>

            <div class="grid gap-6">
                @if ($successMessage)
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800 shadow-sm">
                        {{ $successMessage }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800 shadow-sm">
                        <p class="font-semibold">Please fix the highlighted fields.</p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid gap-6">
                    @unless ($isRegister)
                        <article class="rounded-3xl border border-white/80 bg-white/90 p-6 shadow-[0_24px_70px_rgba(24,24,27,0.12)] backdrop-blur">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-primary">{{ __('ui.sign_in') }}</p>
                                    <h2 class="mt-2 text-2xl font-semibold text-zinc-950">{{ __('ui.welcome_back') }}</h2>
                                </div>
                                <span class="rounded-full bg-brand-surface px-3 py-1 text-xs font-semibold text-brand-ink">{{ __('ui.existing_customer') }}</span>
                            </div>

                            <form class="mt-6 space-y-4" method="POST" action="{{ route('login.submit') }}">
                                @csrf

                                <div>
                                    <label class="text-sm font-semibold text-zinc-800" for="login">{{ __('ui.username_or_email') }}</label>
                                    <input
                                        class="mt-2 min-h-12 w-full rounded-2xl border border-zinc-200 bg-white px-4 text-sm outline-none transition focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                        id="login"
                                        name="login"
                                        type="text"
                                        value="{{ old('login') }}"
                                        autocomplete="username"
                                        required
                                    >
                                    @error('login')
                                        <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-zinc-800" for="login-password">{{ __('ui.password') }}</label>
                                    <input
                                        class="mt-2 min-h-12 w-full rounded-2xl border border-zinc-200 bg-white px-4 text-sm outline-none transition focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10"
                                        id="login-password"
                                        name="password"
                                        type="password"
                                        autocomplete="current-password"
                                        required
                                    >
                                    @error('password')
                                        <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <label class="flex items-center gap-3 text-sm text-zinc-600">
                                        <input class="h-4 w-4 rounded border-zinc-300 text-brand-primary focus:ring-brand-primary" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                                        {{ __('ui.remember_me') }}
                                    </label>

                                    <a class="text-sm font-semibold text-brand-primary transition hover:opacity-80" href="{{ route('password.request') }}">
                                        {{ __('ui.forgot_password') }}
                                    </a>
                                </div>

                                <button class="inline-flex min-h-12 w-full items-center justify-center rounded-2xl bg-brand-ink px-5 text-sm font-semibold text-white transition hover:bg-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/20" type="submit">
                                    {{ __('ui.sign_in') }}
                                </button>
                            </form>
                        </article>
                    @endunless

                    @if ($isRegister)
                        <article class="rounded-3xl border border-brand-primary/10 bg-brand-ink p-6 text-white shadow-[0_24px_70px_rgba(24,24,27,0.22)]">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-accent">{{ __('ui.register') }}</p>
                                    <h2 class="mt-2 text-2xl font-semibold">{{ __('ui.sign_in_account') }}</h2>
                                </div>
                                <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white/80">{{ __('ui.new_customer') }}</span>
                            </div>

                            <form class="mt-6 space-y-4" method="POST" action="{{ route('register.submit') }}">
                                @csrf

                                <div>
                                    <label class="text-sm font-semibold text-white/90" for="full_name">{{ __('ui.full_name') }}</label>
                                    <input
                                        class="mt-2 min-h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm text-white outline-none transition placeholder:text-white/35 focus:border-brand-accent focus:ring-4 focus:ring-brand-accent/20"
                                        id="full_name"
                                        name="full_name"
                                        type="text"
                                        value="{{ old('full_name') }}"
                                        autocomplete="name"
                                        required
                                    >
                                    @error('full_name')
                                        <p class="mt-2 text-sm text-amber-200">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="text-sm font-semibold text-white/90" for="username">{{ __('ui.username_or_email') }}</label>
                                        <input
                                            class="mt-2 min-h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm text-white outline-none transition placeholder:text-white/35 focus:border-brand-accent focus:ring-4 focus:ring-brand-accent/20"
                                            id="username"
                                            name="username"
                                            type="text"
                                            value="{{ old('username') }}"
                                            autocomplete="username"
                                            required
                                        >
                                        @error('username')
                                            <p class="mt-2 text-sm text-amber-200">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-white/90" for="email">{{ __('ui.email_address') }}</label>
                                        <input
                                            class="mt-2 min-h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm text-white outline-none transition placeholder:text-white/35 focus:border-brand-accent focus:ring-4 focus:ring-brand-accent/20"
                                            id="email"
                                            name="email"
                                            type="email"
                                            value="{{ old('email') }}"
                                            autocomplete="email"
                                            required
                                        >
                                        @error('email')
                                            <p class="mt-2 text-sm text-amber-200">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="text-sm font-semibold text-white/90" for="register-password">{{ __('ui.password') }}</label>
                                        <input
                                            class="mt-2 min-h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm text-white outline-none transition placeholder:text-white/35 focus:border-brand-accent focus:ring-4 focus:ring-brand-accent/20"
                                            id="register-password"
                                            name="password"
                                            type="password"
                                            autocomplete="new-password"
                                            required
                                        >
                                        @error('password')
                                            <p class="mt-2 text-sm text-amber-200">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-white/90" for="password_confirmation">{{ __('ui.confirm_password') }}</label>
                                        <input
                                            class="mt-2 min-h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm text-white outline-none transition placeholder:text-white/35 focus:border-brand-accent focus:ring-4 focus:ring-brand-accent/20"
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            type="password"
                                            autocomplete="new-password"
                                            required
                                        >
                                    </div>
                                </div>

                                <button class="inline-flex min-h-12 w-full items-center justify-center rounded-2xl bg-brand-accent px-5 text-sm font-semibold text-brand-ink transition hover:opacity-95 focus:outline-none focus:ring-4 focus:ring-brand-accent/25" type="submit">
                                    {{ __('ui.register') }}
                                </button>
                            </form>
                        </article>
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-white/70 bg-white/80 px-5 py-4 text-sm text-zinc-600 shadow-sm backdrop-blur">
                    @if ($isRegister)
                        <p>
                            Already have an account?
                            <a class="font-semibold text-brand-primary transition hover:opacity-80" href="{{ route('login') }}">{{ __('ui.sign_in') }}</a>.
                        </p>
                    @else
                        <p>
                            {{ __('ui.new_customer') }}?
                            <a class="font-semibold text-brand-primary transition hover:opacity-80" href="{{ route('register') }}">{{ __('ui.register') }}</a>.
                        </p>
                    @endif
                    <a class="font-semibold text-brand-primary transition hover:opacity-80" href="{{ route('home') }}">{{ __('ui.home') }}</a>
                </div>
            </div>
        </div>
    </section>
</x-site.layout>
