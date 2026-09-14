<x-admin.layout title="Sort Products Management">
    <div class="mx-auto w-full max-w-[90rem] px-6 py-8 lg:px-8 space-y-8">
        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-red-600">
                    <span>Admin Control</span>
                    <span>&bull;</span>
                    <span>Storefront Sorting</span>
                </div>
                <h1 class="mt-1 text-2xl font-bold text-zinc-950 sm:text-3xl">Sort Products Management</h1>
                <p class="mt-1 text-sm text-zinc-600">
                    Fully control the Sort Products dropdown on the Home Page. Add, reorder, toggle, or delete sorting options in real time.
                </p>
            </div>
        </div>

        {{-- Status / Alerts --}}
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('status') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-950 text-xs font-bold uppercase">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800 shadow-sm">
                <p class="font-bold">Please check the following errors:</p>
                <ul class="mt-1 list-inside list-disc text-xs font-normal text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Total Sort Options</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950">{{ $stats['total'] }}</p>
                <p class="mt-1 text-[11px] text-zinc-500">Configured in database</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Active on Home Page</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $stats['active'] }}</p>
                <p class="mt-1 text-[11px] text-emerald-600 font-medium">Visible to customers</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Disabled / Inactive</p>
                <p class="mt-2 text-2xl font-bold text-zinc-600">{{ $stats['inactive'] }}</p>
                <p class="mt-1 text-[11px] text-zinc-500">Hidden from storefront</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-600">Default Sort</p>
                <p class="mt-2 text-base font-bold text-amber-800 truncate" title="{{ $stats['default']?->label ?? 'None' }}">
                    {{ $stats['default']?->label ?? 'None' }}
                </p>
                <p class="mt-1 text-[11px] text-amber-700">Pre-selected for visitors</p>
            </div>
        </div>

        {{-- Live Synchronization Helper Card --}}
        <div class="rounded-2xl border border-amber-200/80 bg-gradient-to-r from-amber-50/60 via-white to-amber-50/30 p-5 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-brand-primary">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" /></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-sm font-bold text-zinc-950">Dynamic Home Page Synchronization</h2>
                    <p class="mt-0.5 text-xs text-zinc-600 leading-relaxed">
                        Any changes made here take effect immediately on the Home Page product catalog. Disabled or removed options automatically vanish from the dropdown. Reordering options alters the sequence in which customers browse.
                    </p>
                </div>
                <a href="{{ route('home') }}#products" target="_blank" class="shrink-0 inline-flex items-center gap-1 text-xs font-bold text-brand-primary hover:underline">
                    <span>View Storefront</span>
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                </a>
            </div>
        </div>

        {{-- Sort Options Table Card --}}
        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-zinc-100 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-zinc-950">Available Sort Options</h2>
                    <p class="text-xs text-zinc-500">Ordered by display sequence on the storefront (Showing <span class="font-bold text-zinc-900">{{ $sortOptions->count() }}</span> options)</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <form action="{{ route('admin.sort-options.reset') }}" method="POST" onsubmit="return confirm('Reset all sorting options back to default recommended presets? Custom options may be overwritten.');">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50 hover:text-zinc-950"
                        >
                            <svg class="h-4 w-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                            <span>Reset Defaults</span>
                        </button>
                    </form>

                    <button
                        type="button"
                        onclick="openCreateModal()"
                        class="inline-flex items-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <span>Add Sort Option</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-600">
                    <thead class="bg-zinc-50/80 text-[11px] font-bold uppercase tracking-wider text-zinc-500 border-b border-zinc-200/70">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 w-24">Order</th>
                            <th scope="col" class="px-6 py-3.5">Option Label & Key</th>
                            <th scope="col" class="px-6 py-3.5">Sort Rule & Direction</th>
                            <th scope="col" class="px-6 py-3.5">Status</th>
                            <th scope="col" class="px-6 py-3.5">Default</th>
                            <th scope="col" class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($sortOptions as $index => $option)
                            <tr class="hover:bg-zinc-50/60 transition {{ ! $option->is_active ? 'opacity-60 bg-zinc-50/30' : '' }}">
                                {{-- Display Order & Move Controls --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-zinc-100 text-xs font-bold text-zinc-800">
                                            {{ $option->display_order }}
                                        </span>
                                        <div class="flex flex-col">
                                            @if (! $loop->first)
                                                <form action="{{ route('admin.sort-options.move', $option) }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="direction" value="up">
                                                    <button type="submit" title="Move Up" class="p-0.5 text-zinc-400 hover:text-zinc-900 transition">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                                                    </button>
                                                </form>
                                            @endif
                                            @if (! $loop->last)
                                                <form action="{{ route('admin.sort-options.move', $option) }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="direction" value="down">
                                                    <button type="submit" title="Move Down" class="p-0.5 text-zinc-400 hover:text-zinc-900 transition">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Label & Key --}}
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-zinc-950 text-sm">{{ $option->label }}</span>
                                            @if ($option->is_system)
                                                <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] font-semibold text-zinc-600">Preset</span>
                                            @endif
                                        </div>
                                        <span class="font-mono text-xs text-zinc-400 mt-0.5">key: <code class="text-zinc-600 font-semibold">{{ $option->key }}</code></span>
                                    </div>
                                </td>

                                {{-- Sort Rule & Direction --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-900 border border-amber-200/60">
                                            @if ($option->sort_field === 'is_featured')
                                                <svg class="h-3.5 w-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                                                Featured First
                                            @elseif ($option->sort_field === 'best_selling')
                                                <svg class="h-3.5 w-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" /></svg>
                                                Total Sales Units
                                            @elseif ($option->sort_field === 'discount')
                                                <svg class="h-3.5 w-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                Highest Savings
                                            @else
                                                <span class="font-mono">{{ $option->sort_field }}</span>
                                            @endif
                                        </span>
                                        <span class="rounded bg-zinc-100 px-2 py-0.5 text-[11px] font-bold uppercase text-zinc-700">
                                            {{ $option->sort_direction }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Status (Active Toggle) --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form action="{{ route('admin.sort-options.toggle', $option) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold transition shadow-sm {{ $option->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}"
                                            title="Click to toggle status"
                                        >
                                            <span class="h-1.5 w-1.5 rounded-full {{ $option->is_active ? 'bg-emerald-600' : 'bg-zinc-400' }}"></span>
                                            <span>{{ $option->is_active ? 'Active' : 'Disabled' }}</span>
                                        </button>
                                    </form>
                                </td>

                                {{-- Default Badge / Set as Default --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($option->is_default)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-900 border border-amber-300/60 shadow-sm">
                                            <svg class="h-3.5 w-3.5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                            Default
                                        </span>
                                    @else
                                        <form action="{{ route('admin.sort-options.set-default', $option) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="text-xs font-semibold text-zinc-500 hover:text-amber-700 transition"
                                                title="Make this the initial default sort"
                                            >
                                                Make Default
                                            </button>
                                        </form>
                                    @endif
                                </td>

                                {{-- Actions (Edit, Delete) --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            onclick='openEditModal(@json($option))'
                                            class="rounded-lg border border-zinc-200 p-1.5 text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-950"
                                            title="Edit Sort Option"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                        </button>

                                        <form
                                            action="{{ route('admin.sort-options.destroy', $option) }}"
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete sort option &quot;{{ $option->label }}&quot;? It will be removed from the Home Page.');"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="rounded-lg border border-zinc-200 p-1.5 text-red-600 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700"
                                                title="Delete Sort Option"
                                            >
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-zinc-500">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 mb-3">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" /></svg>
                                    </div>
                                    <p class="font-bold text-zinc-800 text-base">No sort options found</p>
                                    <p class="text-xs text-zinc-500 mt-1">Click "Reset Defaults" or "Add Sort Option" to configure sorting.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Create Sort Option Modal --}}
    <div id="create-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-zinc-950/60 backdrop-blur-sm p-4 flex items-center justify-center">
        <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl transition-all">
            <div class="flex items-center justify-between border-b border-zinc-100 pb-4">
                <div>
                    <h3 class="text-lg font-bold text-zinc-950">Add New Sort Option</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">Configure how products are sorted when selected</p>
                </div>
                <button type="button" onclick="closeCreateModal()" class="rounded-lg p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form action="{{ route('admin.sort-options.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf

                {{-- Quick Presets --}}
                <div>
                    <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1.5">Quick Presets (Optional)</label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <button type="button" onclick="applyPreset('Best Selling', 'best_selling', 'best_selling', 'desc')" class="rounded-lg border border-zinc-200 p-2 text-left hover:bg-amber-50 hover:border-amber-300 transition">
                            <p class="font-bold text-zinc-900">🔥 Best Selling</p>
                            <p class="text-[10px] text-zinc-500">Highest sales volume</p>
                        </button>
                        <button type="button" onclick="applyPreset('Top Rated', 'rating', 'rating', 'desc')" class="rounded-lg border border-zinc-200 p-2 text-left hover:bg-amber-50 hover:border-amber-300 transition">
                            <p class="font-bold text-zinc-900">⭐ Top Rated</p>
                            <p class="text-[10px] text-zinc-500">Customer star reviews</p>
                        </button>
                        <button type="button" onclick="applyPreset('Newest Arrivals', 'newest', 'created_at', 'desc')" class="rounded-lg border border-zinc-200 p-2 text-left hover:bg-amber-50 hover:border-amber-300 transition">
                            <p class="font-bold text-zinc-900">✨ Newest Arrivals</p>
                            <p class="text-[10px] text-zinc-500">Latest added spices</p>
                        </button>
                        <button type="button" onclick="applyPreset('Biggest Discounts', 'discount', 'discount', 'desc')" class="rounded-lg border border-zinc-200 p-2 text-left hover:bg-amber-50 hover:border-amber-300 transition">
                            <p class="font-bold text-zinc-900">🏷️ Big Discounts</p>
                            <p class="text-[10px] text-zinc-500">Maximum price drops</p>
                        </button>
                    </div>
                </div>

                {{-- Option Label --}}
                <div>
                    <label for="create_label" class="block text-xs font-bold text-zinc-900 uppercase tracking-wider">Option Label *</label>
                    <input
                        type="text"
                        name="label"
                        id="create_label"
                        required
                        placeholder="e.g. Best Selling, Price: Low to High"
                        class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                    >
                </div>

                {{-- Option Key (Slug) --}}
                <div>
                    <label for="create_key" class="block text-xs font-bold text-zinc-900 uppercase tracking-wider">Sort Key (URL slug)</label>
                    <input
                        type="text"
                        name="key"
                        id="create_key"
                        placeholder="e.g. best_selling (leave blank to auto-generate)"
                        class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600 font-mono text-xs"
                    >
                </div>

                {{-- Field & Direction --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="create_sort_field" class="block text-xs font-bold text-zinc-900 uppercase tracking-wider">Sort Field *</label>
                        <select
                            name="sort_field"
                            id="create_sort_field"
                            required
                            class="mt-1 block w-full rounded-xl border border-zinc-300 px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                        >
                            <option value="is_featured">Featured Priority</option>
                            <option value="price">Price (₹)</option>
                            <option value="rating">Rating (Stars)</option>
                            <option value="created_at">Date Added / Newest</option>
                            <option value="best_selling">Best Selling (Sales Volume)</option>
                            <option value="discount">Discount / Price Difference</option>
                            <option value="name">Alphabetical (Name)</option>
                            <option value="quantity">Stock / Inventory</option>
                            <option value="priority">Admin Priority Weight</option>
                        </select>
                    </div>

                    <div>
                        <label for="create_sort_direction" class="block text-xs font-bold text-zinc-900 uppercase tracking-wider">Direction *</label>
                        <select
                            name="sort_direction"
                            id="create_sort_direction"
                            required
                            class="mt-1 block w-full rounded-xl border border-zinc-300 px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                        >
                            <option value="asc">Ascending (Low &rarr; High / A &rarr; Z)</option>
                            <option value="desc" selected>Descending (High &rarr; Low / Z &rarr; A)</option>
                        </select>
                    </div>
                </div>

                {{-- Display Order --}}
                <div>
                    <label for="create_display_order" class="block text-xs font-bold text-zinc-900 uppercase tracking-wider">Display Order</label>
                    <input
                        type="number"
                        name="display_order"
                        id="create_display_order"
                        value="{{ ($sortOptions->max('display_order') ?? 0) + 1 }}"
                        min="0"
                        class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                    >
                </div>

                {{-- Checkboxes --}}
                <div class="space-y-2 pt-2 border-t border-zinc-100">
                    <label class="flex items-center gap-2 text-xs font-semibold text-zinc-800 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-zinc-300 text-red-600 focus:ring-red-500">
                        <span>Active (Display immediately on Home Page)</span>
                    </label>

                    <label class="flex items-center gap-2 text-xs font-semibold text-zinc-800 cursor-pointer">
                        <input type="checkbox" name="is_default" value="1" class="h-4 w-4 rounded border-zinc-300 text-red-600 focus:ring-red-500">
                        <span>Set as Default Selection for new visitors</span>
                    </label>
                </div>

                {{-- Form Actions --}}
                <div class="mt-6 flex items-center justify-end gap-2 border-t border-zinc-100 pt-4">
                    <button
                        type="button"
                        onclick="closeCreateModal()"
                        class="rounded-xl border border-zinc-300 bg-white px-4 py-2 text-xs font-semibold text-zinc-700 shadow-sm hover:bg-zinc-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-xl bg-zinc-950 px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-700 transition"
                    >
                        Create Option
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Sort Option Modal --}}
    <div id="edit-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-zinc-950/60 backdrop-blur-sm p-4 flex items-center justify-center">
        <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl transition-all">
            <div class="flex items-center justify-between border-b border-zinc-100 pb-4">
                <div>
                    <h3 class="text-lg font-bold text-zinc-950">Edit Sort Option</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">Modify sort label, key, or criteria</p>
                </div>
                <button type="button" onclick="closeEditModal()" class="rounded-lg p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form id="edit-form" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                {{-- Option Label --}}
                <div>
                    <label for="edit_label" class="block text-xs font-bold text-zinc-900 uppercase tracking-wider">Option Label *</label>
                    <input
                        type="text"
                        name="label"
                        id="edit_label"
                        required
                        class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                    >
                </div>

                {{-- Option Key (Slug) --}}
                <div>
                    <label for="edit_key" class="block text-xs font-bold text-zinc-900 uppercase tracking-wider">Sort Key *</label>
                    <input
                        type="text"
                        name="key"
                        id="edit_key"
                        required
                        class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600 font-mono text-xs"
                    >
                </div>

                {{-- Field & Direction --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="edit_sort_field" class="block text-xs font-bold text-zinc-900 uppercase tracking-wider">Sort Field *</label>
                        <select
                            name="sort_field"
                            id="edit_sort_field"
                            required
                            class="mt-1 block w-full rounded-xl border border-zinc-300 px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                        >
                            <option value="is_featured">Featured Priority</option>
                            <option value="price">Price (₹)</option>
                            <option value="rating">Rating (Stars)</option>
                            <option value="created_at">Date Added / Newest</option>
                            <option value="best_selling">Best Selling (Sales Volume)</option>
                            <option value="discount">Discount / Price Difference</option>
                            <option value="name">Alphabetical (Name)</option>
                            <option value="quantity">Stock / Inventory</option>
                            <option value="priority">Admin Priority Weight</option>
                        </select>
                    </div>

                    <div>
                        <label for="edit_sort_direction" class="block text-xs font-bold text-zinc-900 uppercase tracking-wider">Direction *</label>
                        <select
                            name="sort_direction"
                            id="edit_sort_direction"
                            required
                            class="mt-1 block w-full rounded-xl border border-zinc-300 px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                        >
                            <option value="asc">Ascending (Low &rarr; High / A &rarr; Z)</option>
                            <option value="desc">Descending (High &rarr; Low / Z &rarr; A)</option>
                        </select>
                    </div>
                </div>

                {{-- Display Order --}}
                <div>
                    <label for="edit_display_order" class="block text-xs font-bold text-zinc-900 uppercase tracking-wider">Display Order</label>
                    <input
                        type="number"
                        name="display_order"
                        id="edit_display_order"
                        min="0"
                        class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                    >
                </div>

                {{-- Checkboxes --}}
                <div class="space-y-2 pt-2 border-t border-zinc-100">
                    <label class="flex items-center gap-2 text-xs font-semibold text-zinc-800 cursor-pointer">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="h-4 w-4 rounded border-zinc-300 text-red-600 focus:ring-red-500">
                        <span>Active (Display immediately on Home Page)</span>
                    </label>

                    <label class="flex items-center gap-2 text-xs font-semibold text-zinc-800 cursor-pointer">
                        <input type="checkbox" name="is_default" id="edit_is_default" value="1" class="h-4 w-4 rounded border-zinc-300 text-red-600 focus:ring-red-500">
                        <span>Set as Default Selection for new visitors</span>
                    </label>
                </div>

                {{-- Form Actions --}}
                <div class="mt-6 flex items-center justify-end gap-2 border-t border-zinc-100 pt-4">
                    <button
                        type="button"
                        onclick="closeEditModal()"
                        class="rounded-xl border border-zinc-300 bg-white px-4 py-2 text-xs font-semibold text-zinc-700 shadow-sm hover:bg-zinc-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-xl bg-zinc-950 px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-700 transition"
                    >
                        Update Option
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('create-modal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('create-modal').classList.add('hidden');
        }

        function applyPreset(label, key, field, dir) {
            document.getElementById('create_label').value = label;
            document.getElementById('create_key').value = key;
            document.getElementById('create_sort_field').value = field;
            document.getElementById('create_sort_direction').value = dir;
        }

        function openEditModal(option) {
            const form = document.getElementById('edit-form');
            form.action = `/admin/sort-options/${option.id}`;
            document.getElementById('edit_label').value = option.label || '';
            document.getElementById('edit_key').value = option.key || '';
            document.getElementById('edit_sort_field').value = option.sort_field || 'is_featured';
            document.getElementById('edit_sort_direction').value = option.sort_direction || 'desc';
            document.getElementById('edit_display_order').value = option.display_order ?? 0;
            document.getElementById('edit_is_active').checked = !!option.is_active;
            document.getElementById('edit_is_default').checked = !!option.is_default;

            document.getElementById('edit-modal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
        }

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeCreateModal();
                closeEditModal();
            }
        });
    </script>
</x-admin.layout>
