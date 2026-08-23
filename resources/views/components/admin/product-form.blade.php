@props([
    'action',
    'product' => null,
    'submitLabel' => 'Save product',
])

@php
    $isEditing = $product !== null;
    $highlightValue = old('highlights', $product?->highlights ?? []);
    $highlightValue = is_array($highlightValue) ? implode(PHP_EOL, $highlightValue) : $highlightValue;
@endphp

<form class="mt-6 space-y-4" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="name">Product name</label>
            <input class="admin-input" id="name" name="name" type="text" value="{{ old('name', $product?->name) }}" required>
            @error('name') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="sku">SKU</label>
            <input class="admin-input uppercase" id="sku" name="sku" type="text" value="{{ old('sku', $product?->sku) }}" placeholder="FF-GM-100" required>
            @error('sku') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="category">Category</label>
            <input class="admin-input" id="category" name="category" type="text" value="{{ old('category', $product?->category) }}" placeholder="Pure spice" required>
            @error('category') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="unit">Pack size</label>
            <input class="admin-input" id="unit" name="unit" type="text" value="{{ old('unit', $product?->unit ?? '100 g') }}" placeholder="100 g" required>
            @error('unit') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="text-sm font-semibold text-zinc-800" for="description">Description</label>
        <textarea class="admin-input min-h-24 py-3" id="description" name="description" required>{{ old('description', $product?->description) }}</textarea>
        <p class="mt-1 text-xs text-zinc-500">Short, attractive copy used on product cards.</p>
        @error('description') <p class="admin-error">{{ $message }}</p> @enderror
    </div>

    <div class="border-t border-zinc-200 pt-5">
        <p class="text-xs font-semibold uppercase text-emerald-700">Full product page</p>
        <p class="mt-1 text-xs leading-5 text-zinc-500">These details appear only after a customer opens the product.</p>
    </div>

    <div>
        <label class="text-sm font-semibold text-zinc-800" for="long_description">Full description</label>
        <textarea class="admin-input min-h-32 py-3" id="long_description" name="long_description">{{ old('long_description', $product?->long_description) }}</textarea>
        @error('long_description') <p class="admin-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-zinc-800" for="highlights">Product highlights</label>
        <textarea class="admin-input min-h-28 py-3" id="highlights" name="highlights" placeholder="Freshly packed&#10;No artificial colours&#10;Balanced everyday flavour">{{ $highlightValue }}</textarea>
        <p class="mt-1 text-xs text-zinc-500">Add one highlight per line, up to six.</p>
        @error('highlights') <p class="admin-error">{{ $message }}</p> @enderror
        @error('highlights.*') <p class="admin-error">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="ingredients">Ingredients</label>
            <textarea class="admin-input min-h-28 py-3" id="ingredients" name="ingredients">{{ old('ingredients', $product?->ingredients) }}</textarea>
            @error('ingredients') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="usage_instructions">How to use</label>
            <textarea class="admin-input min-h-28 py-3" id="usage_instructions" name="usage_instructions">{{ old('usage_instructions', $product?->usage_instructions) }}</textarea>
            @error('usage_instructions') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="text-sm font-semibold text-zinc-800" for="origin">Origin / packed at</label>
        <input class="admin-input" id="origin" name="origin" type="text" value="{{ old('origin', $product?->origin) }}" placeholder="Blended and packed in Gujarat, India">
        @error('origin') <p class="admin-error">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="price">Selling price</label>
            <input class="admin-input" id="price" name="price" type="number" value="{{ old('price', $product?->price) }}" min="0" step="0.01" required>
            @error('price') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="compare_at_price">MRP / compare price</label>
            <input class="admin-input" id="compare_at_price" name="compare_at_price" type="number" value="{{ old('compare_at_price', $product?->compare_at_price) }}" min="0" step="0.01">
            @error('compare_at_price') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="quantity">Quantity</label>
            <input class="admin-input" id="quantity" name="quantity" type="number" value="{{ old('quantity', $product?->quantity ?? 0) }}" min="0" max="1000000" required>
            @error('quantity') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="low_stock_threshold">Low-stock alert at</label>
            <input class="admin-input" id="low_stock_threshold" name="low_stock_threshold" type="number" value="{{ old('low_stock_threshold', $product?->low_stock_threshold ?? 5) }}" min="0" max="1000000" required>
            @error('low_stock_threshold') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="rating">Rating</label>
            <input class="admin-input" id="rating" name="rating" type="number" value="{{ old('rating', $product?->rating ?? '4.5') }}" min="0" max="5" step="0.1" required>
            @error('rating') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="priority">Priority</label>
            <input class="admin-input" id="priority" name="priority" type="number" value="{{ old('priority', $product?->priority ?? 50) }}" min="0" max="100" required>
            @error('priority') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-zinc-800" for="badge">Badge</label>
            <input class="admin-input" id="badge" name="badge" type="text" value="{{ old('badge', $product?->badge ?? 'New') }}" required>
            @error('badge') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="text-sm font-semibold text-zinc-800" for="image">{{ $isEditing ? 'Replace product image' : 'Product image' }}</label>
        <input class="mt-2 block w-full text-sm text-zinc-600 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-3 file:text-sm file:font-semibold file:text-zinc-800 hover:file:bg-zinc-200" id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp">
        @error('image') <p class="admin-error">{{ $message }}</p> @enderror

        @if ($isEditing && $product->image_path)
            <div class="mt-3 flex items-center gap-3 rounded-lg border border-zinc-200 p-3">
                <img class="h-12 w-12 rounded-lg object-cover" src="{{ asset($product->image_path) }}" alt="{{ $product->name }}">
                <p class="text-xs text-zinc-500">Current image remains unless a replacement is selected.</p>
            </div>
        @endif
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <label class="flex min-h-12 items-center gap-3 rounded-lg border border-zinc-200 px-3 text-sm text-zinc-700">
            <input class="h-4 w-4 rounded border-zinc-300 text-red-600 focus:ring-red-500" name="is_featured" type="checkbox" value="1" @checked(old('is_featured', $product?->is_featured ?? false))>
            Hero featured
        </label>
        <label class="flex min-h-12 items-center gap-3 rounded-lg border border-zinc-200 px-3 text-sm text-zinc-700">
            <input class="h-4 w-4 rounded border-zinc-300 text-red-600 focus:ring-red-500" name="is_active" type="checkbox" value="1" @checked(old('is_active', $product?->is_active ?? true))>
            Active
        </label>
    </div>

    <button class="inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-red-700 px-5 text-sm font-semibold text-white transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300 focus:ring-offset-2" type="submit">
        {{ $submitLabel }}
    </button>
</form>
