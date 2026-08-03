<x-admin.layout :title="$title">
    <main class="mx-auto w-full max-w-[90rem] px-6 py-8 lg:px-8">
        <section class="rounded-lg border border-zinc-200 bg-white p-8">
            <p class="text-sm font-semibold text-red-700">Admin section</p>
            <h1 class="mt-2 text-3xl font-semibold text-zinc-950">{{ $headline }}</h1>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-zinc-600">{{ $description }}</p>
            <div class="mt-6 rounded-lg border border-dashed border-zinc-300 bg-zinc-50 p-5 text-sm text-zinc-500">
                This is a placeholder menu for now. We can wire the full management screen into this slot next.
            </div>
        </section>
    </main>
</x-admin.layout>
