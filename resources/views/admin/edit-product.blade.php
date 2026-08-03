<x-admin.layout title="Edit {{ $product->name }}">
    <main class="mx-auto w-full max-w-4xl px-6 py-8 lg:px-8">
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
