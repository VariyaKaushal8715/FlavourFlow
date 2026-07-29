<x-admin.layout title="Offers">
    <x-admin.header active="offers" />

    <main class="mx-auto grid w-full max-w-[90rem] gap-8 px-6 py-8 lg:grid-cols-[24rem_1fr] lg:px-8">
        <section class="self-start rounded-lg border border-zinc-200 bg-white p-6">
            <p class="text-sm font-semibold text-red-700">New campaign</p>
            <h1 class="mt-2 text-2xl font-semibold text-zinc-950">Add special offer</h1>
            <p class="mt-2 text-sm leading-6 text-zinc-600">Create seasonal, daily, or limited-time offers and schedule when they appear.</p>

            @if ($errors->any())
                <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    Please review the highlighted fields.
                </div>
            @endif

            <x-admin.offer-form
                :action="route('admin.offers.store')"
                submit-label="Add offer"
            />
        </section>

        <section class="min-w-0">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-emerald-700">Campaign control</p>
                    <h2 class="mt-2 text-2xl font-semibold text-zinc-950">Special offers</h2>
                    <p class="mt-2 text-sm text-zinc-600">Manage timing, coupon codes, visibility, and homepage prominence.</p>
                </div>
                <a class="text-sm font-semibold text-zinc-700 transition hover:text-red-700" href="{{ route('home').'#offers' }}" target="_blank" rel="noreferrer">View live offers &rarr;</a>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-3">
                <div class="rounded-lg border border-zinc-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase text-zinc-500">Total offers</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-950">{{ $offerStats->total }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase text-zinc-500">Live now</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $offerStats->live }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase text-zinc-500">Featured</p>
                    <p class="mt-2 text-2xl font-semibold text-amber-700">{{ $offerStats->featured }}</p>
                </div>
            </div>

            @if (session('status'))
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form class="mt-6 grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 sm:grid-cols-[1fr_auto]" method="GET" action="{{ route('admin.offers.index') }}">
                <div>
                    <label class="sr-only" for="search">Search offers</label>
                    <input class="admin-input mt-0" id="search" name="search" type="search" value="{{ $search }}" placeholder="Search title, coupon, or discount">
                </div>
                <button class="min-h-12 rounded-lg bg-zinc-950 px-5 text-sm font-semibold text-white transition hover:bg-red-700" type="submit">Search</button>
            </form>

            @if ($search)
                <div class="mt-3 flex justify-end">
                    <a class="text-sm font-semibold text-zinc-500 transition hover:text-red-700" href="{{ route('admin.offers.index') }}">Clear search</a>
                </div>
            @endif

            <div class="mt-6 overflow-hidden rounded-lg border border-zinc-200 bg-white">
                @forelse ($offers as $offer)
                    <article class="grid gap-4 border-b border-zinc-100 p-4 last:border-b-0 md:grid-cols-[7rem_1fr_auto] md:items-center">
                        <img class="aspect-[4/3] h-20 w-28 rounded-lg object-cover" src="{{ asset($offer->image_path ?: 'images/flavourflow-spice-hero.png') }}" alt="{{ $offer->title }}">

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-semibold text-zinc-950">{{ $offer->title }}</h3>
                                @if ($offer->is_featured)
                                    <span class="rounded-lg bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Featured</span>
                                @endif
                                <span @class([
                                    'rounded-lg px-2 py-1 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-800' => $offer->statusLabel() === 'Live',
                                    'bg-blue-100 text-blue-800' => $offer->statusLabel() === 'Scheduled',
                                    'bg-zinc-100 text-zinc-600' => in_array($offer->statusLabel(), ['Inactive', 'Expired'], true),
                                ])>{{ $offer->statusLabel() }}</span>
                            </div>
                            <p class="mt-1 truncate text-sm text-zinc-500">
                                {{ $offer->eyebrow }} &middot; {{ $offer->discount_label }} &middot; Priority {{ $offer->priority }}
                            </p>
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-zinc-600">{{ $offer->description }}</p>
                            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-xs font-semibold text-zinc-600">
                                <span>{{ $offer->dateRangeLabel() }}</span>
                                @if ($offer->coupon_code)
                                    <span>Code: {{ $offer->coupon_code }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 md:flex-col md:items-end">
                            <a class="inline-flex min-h-10 items-center justify-center rounded-lg border border-zinc-300 px-4 text-sm font-semibold text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" href="{{ route('admin.offers.edit', $offer) }}">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.offers.destroy', $offer) }}" data-confirm-delete data-item-name="{{ $offer->title }}">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex min-h-10 items-center justify-center rounded-lg border border-red-200 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-50" type="submit">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="p-10 text-center">
                        <p class="font-semibold text-zinc-950">No matching offers</p>
                        <p class="mt-2 text-sm text-zinc-500">Change the search or add a new campaign.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $offers->links() }}
            </div>
        </section>
    </main>
</x-admin.layout>
