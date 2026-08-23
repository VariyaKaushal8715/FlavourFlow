@props([
    'action',
    'offer' => null,
    'submitLabel' => 'Save offer',
])

@php
    $isEditing = $offer !== null;
@endphp

<form class="mt-6 space-y-4" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <div>
        <label class="text-sm font-semibold text-zinc-800" for="eyebrow">Offer type</label>
        <input class="admin-input" id="eyebrow" name="eyebrow" type="text" value="{{ old('eyebrow', $offer?->eyebrow ?? 'Limited-time offer') }}" placeholder="Monsoon special" required>
        @error('eyebrow') <p class="admin-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-zinc-800" for="title">Offer title</label>
        <input class="admin-input" id="title" name="title" type="text" value="{{ old('title', $offer?->title) }}" required>
        @error('title') <p class="admin-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-zinc-800" for="description">Description</label>
        <textarea class="admin-input min-h-28 py-3" id="description" name="description" required>{{ old('description', $offer?->description) }}</textarea>
        @error('description') <p class="admin-error">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="discount_label">Value label</label>
            <input class="admin-input" id="discount_label" name="discount_label" type="text" value="{{ old('discount_label', $offer?->discount_label) }}" placeholder="Save 20%" required>
            @error('discount_label') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="coupon_code">Coupon code</label>
            <input class="admin-input uppercase" id="coupon_code" name="coupon_code" type="text" value="{{ old('coupon_code', $offer?->coupon_code) }}" placeholder="MONSOON20">
            @error('coupon_code') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="text-sm font-semibold text-zinc-800" for="terms">Short terms</label>
        <input class="admin-input" id="terms" name="terms" type="text" value="{{ old('terms', $offer?->terms) }}" placeholder="Valid above Rs. 499 while stocks last">
        @error('terms') <p class="admin-error">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="starts_at">Starts at</label>
            <input class="admin-input" id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', $offer?->starts_at?->format('Y-m-d\TH:i')) }}">
            @error('starts_at') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="ends_at">Ends at</label>
            <input class="admin-input" id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', $offer?->ends_at?->format('Y-m-d\TH:i')) }}">
            @error('ends_at') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-[1fr_7rem]">
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="image">{{ $isEditing ? 'Replace offer image' : 'Offer image' }}</label>
            <input class="mt-2 block w-full text-sm text-zinc-600 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-3 file:text-sm file:font-semibold file:text-zinc-800 hover:file:bg-zinc-200" id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp">
            @error('image') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="priority">Priority</label>
            <input class="admin-input" id="priority" name="priority" type="number" value="{{ old('priority', $offer?->priority ?? 50) }}" min="0" max="100" required>
            @error('priority') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>

    @if ($isEditing && $offer->image_path)
        <div class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3">
            <img class="h-12 w-12 rounded-lg object-cover" src="{{ asset($offer->image_path) }}" alt="{{ $offer->title }}">
            <p class="text-xs text-zinc-500">Current image remains unless a replacement is selected.</p>
        </div>
    @endif

    <div class="grid gap-3 sm:grid-cols-2">
        <label class="flex min-h-12 items-center gap-3 rounded-lg border border-zinc-200 px-3 text-sm text-zinc-700">
            <input class="h-4 w-4 rounded border-zinc-300 text-red-600 focus:ring-red-500" name="is_featured" type="checkbox" value="1" @checked(old('is_featured', $offer?->is_featured ?? false))>
            Featured offer
        </label>
        <label class="flex min-h-12 items-center gap-3 rounded-lg border border-zinc-200 px-3 text-sm text-zinc-700">
            <input class="h-4 w-4 rounded border-zinc-300 text-red-600 focus:ring-red-500" name="is_active" type="checkbox" value="1" @checked(old('is_active', $offer?->is_active ?? true))>
            Active
        </label>
    </div>

    <button class="inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-red-700 px-5 text-sm font-semibold text-white transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300 focus:ring-offset-2" type="submit">
        {{ $submitLabel }}
    </button>
</form>
