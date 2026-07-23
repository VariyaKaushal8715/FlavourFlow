@props(['eyebrow', 'title', 'description'])

<div class="max-w-3xl">
    <p class="text-sm font-semibold text-red-600">{{ $eyebrow }}</p>
    <h2 class="mt-3 text-3xl font-semibold leading-tight text-zinc-950 sm:text-4xl">{{ $title }}</h2>
    <p class="mt-4 text-base leading-8 text-zinc-600 sm:text-lg">{{ $description }}</p>
</div>
