<x-admin.layout title="Edit {{ $product->name }}">
    <main class="mx-auto w-full max-w-4xl px-6 py-8 lg:px-8">
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs font-semibold text-zinc-500">
                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-1 text-zinc-600 hover:text-red-700 transition">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Products Catalog
                </a>
                <span>/</span>
                <span class="text-zinc-900 truncate max-w-xs">{{ $product->name }}</span>
            </div>
            <a href="{{ route('admin.analytics.products.show', $product) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-zinc-600 hover:text-red-700 transition">
                Performance Analytics →
            </a>
        </div>

        <section class="rounded-lg border border-zinc-200 bg-white p-6 sm:p-8">
            <p class="text-sm font-semibold text-red-700">Edit product</p>
            <h1 class="mt-2 text-2xl font-semibold text-zinc-950">{{ $product->name }}</h1>
            <p class="mt-2 text-sm leading-6 text-zinc-600">Update catalog, pricing, inventory, visibility, and hero placement.</p>

            @if ($errors->any())
                <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    Please review the highlighted fields.
                </div>
            @endif

            <x-admin.product-form
                :action="route('admin.products.update', $product)"
                :product="$product"
                submit-label="Update product"
            />
        </section>
    </main>
</x-admin.layout>
