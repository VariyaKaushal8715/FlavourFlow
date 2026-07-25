<x-admin.layout title="Edit {{ $offer->title }}">
    <header class="border-b border-zinc-200 bg-white">
        <div class="mx-auto flex w-full max-w-4xl items-center justify-between gap-6 px-6 py-4 lg:px-8">
            <a class="flex items-center gap-3" href="{{ route('admin.offers.index') }}">
                <img class="h-10 w-10 rounded-lg object-cover" src="{{ asset('images/flavourflow-mark.png') }}" alt="FlavourFlow">
                <div>
                    <p class="text-sm font-semibold text-zinc-950">FlavourFlow Admin</p>
                    <p class="text-xs text-zinc-500">Back to offers</p>
                </div>
            </a>

            <a class="text-sm font-semibold text-zinc-600 transition hover:text-red-700" href="{{ route('admin.offers.index') }}">Cancel</a>
        </div>
    </header>

    <main class="mx-auto w-full max-w-4xl px-6 py-8 lg:px-8">
        <section class="rounded-lg border border-zinc-200 bg-white p-6 sm:p-8">
            <p class="text-sm font-semibold text-red-700">Edit offer</p>
            <h1 class="mt-2 text-2xl font-semibold text-zinc-950">{{ $offer->title }}</h1>
            <p class="mt-2 text-sm leading-6 text-zinc-600">Update campaign copy, timing, visibility, and homepage placement.</p>

            @if ($errors->any())
                <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    Please review the highlighted fields.
                </div>
            @endif

            <x-admin.offer-form
                :action="route('admin.offers.update', $offer)"
                :offer="$offer"
                submit-label="Update offer"
            />
        </section>
    </main>
</x-admin.layout>
