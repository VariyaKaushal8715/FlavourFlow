<x-admin.layout title="Sign in">
    <main class="grid min-h-screen lg:grid-cols-[0.9fr_1.1fr]">
        <section class="flex items-center bg-white px-6 py-12 sm:px-10 lg:px-16">
            <div class="mx-auto w-full max-w-md">
                <div class="flex items-center gap-3">
                    <img class="h-11 w-11 rounded-lg object-cover" src="{{ asset('images/flavourflow-mark.png') }}" alt="FlavourFlow">
                    <div>
                        <p class="text-sm font-semibold text-zinc-950">FlavourFlow</p>
                        <p class="text-xs text-zinc-500">Private admin</p>
                    </div>
                </div>

                <div class="mt-12">
                    <p class="text-sm font-semibold text-red-700">Restricted access</p>
                    <h1 class="mt-3 text-3xl font-semibold text-zinc-950">Manage the product collection.</h1>
                    <p class="mt-3 text-sm leading-7 text-zinc-600">Sign in with an administrator account to add products and control the homepage spotlight.</p>
                </div>

                <form class="mt-8 space-y-5" method="POST" action="{{ route('admin.login') }}">
                    @csrf

                    <div>
                        <label class="text-sm font-semibold text-zinc-800" for="email">Email address</label>
                        <input class="mt-2 min-h-12 w-full rounded-lg border border-zinc-300 bg-white px-4 text-sm outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                        @error('email')
                            <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-zinc-800" for="password">Password</label>
                        <input class="mt-2 min-h-12 w-full rounded-lg border border-zinc-300 bg-white px-4 text-sm outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100" id="password" name="password" type="password" autocomplete="current-password" required>
                    </div>

                    <label class="flex items-center gap-3 text-sm text-zinc-600">
                        <input class="h-4 w-4 rounded border-zinc-300 text-red-600 focus:ring-red-500" name="remember" type="checkbox" value="1">
                        Keep me signed in
                    </label>

                    <button class="inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-zinc-950 px-5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 focus:ring-offset-2" type="submit">
                        Sign in to admin
                    </button>
                </form>
            </div>
        </section>

        <aside class="relative hidden overflow-hidden bg-zinc-950 lg:block">
            <img class="absolute inset-0 h-full w-full object-cover opacity-65" src="{{ asset('images/flavourflow-spice-hero.png') }}" alt="">
            <div class="absolute inset-0 bg-black/35"></div>
            <div class="absolute bottom-12 left-12 right-12 border-l-2 border-amber-400 pl-6 text-white">
                <p class="text-sm font-semibold text-amber-300">Product spotlight system</p>
                <p class="mt-3 max-w-xl text-3xl font-semibold leading-tight">Add once. The collection and hero update together.</p>
            </div>
        </aside>
    </main>
</x-admin.layout>
