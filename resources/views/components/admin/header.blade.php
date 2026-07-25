@props(['active'])

<header class="border-b border-zinc-200 bg-white">
    <div class="mx-auto flex w-full max-w-[90rem] flex-col gap-4 px-6 py-4 sm:flex-row sm:items-center sm:justify-between lg:px-8">
        <div class="flex items-center gap-3">
            <img class="h-10 w-10 rounded-lg object-cover" src="{{ asset('images/flavourflow-mark.png') }}" alt="FlavourFlow">
            <div>
                <p class="text-sm font-semibold text-zinc-950">FlavourFlow Admin</p>
                <p class="text-xs text-zinc-500">Catalog, inventory, and offers</p>
            </div>
        </div>

        <div class="flex items-center justify-between gap-5">
            <nav class="flex items-center gap-1 rounded-lg bg-zinc-100 p-1" aria-label="Admin">
                <a @class([
                    'rounded-md px-4 py-2 text-sm font-semibold transition',
                    'bg-white text-zinc-950 shadow-sm' => $active === 'products',
                    'text-zinc-500 hover:text-zinc-950' => $active !== 'products',
                ]) href="{{ route('admin.index') }}">Products</a>
                <a @class([
                    'rounded-md px-4 py-2 text-sm font-semibold transition',
                    'bg-white text-zinc-950 shadow-sm' => $active === 'offers',
                    'text-zinc-500 hover:text-zinc-950' => $active !== 'offers',
                ]) href="{{ route('admin.offers.index') }}">Offers</a>
            </nav>

            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="text-sm font-semibold text-zinc-600 transition hover:text-red-700" type="submit">Sign out</button>
            </form>
        </div>
    </div>
</header>
