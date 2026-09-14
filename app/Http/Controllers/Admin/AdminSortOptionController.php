<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductSortOption;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminSortOptionController extends Controller
{
    /**
     * Display a listing of the sort options.
     */
    public function index(): View
    {
        $sortOptions = ProductSortOption::query()
            ->orderBy('display_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $stats = [
            'total' => $sortOptions->count(),
            'active' => $sortOptions->where('is_active', true)->count(),
            'inactive' => $sortOptions->where('is_active', false)->count(),
            'default' => $sortOptions->firstWhere('is_default', true),
        ];

        return view('admin.sort-options.index', [
            'sortOptions' => $sortOptions,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly created sort option.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'key' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:product_sort_options,key'],
            'sort_field' => ['required', 'string', 'max:50'],
            'sort_direction' => ['required', 'string', 'in:asc,desc'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $key = ! empty($validated['key'])
            ? Str::slug($validated['key'], '_')
            : Str::slug($validated['label'], '_');

        // Ensure key uniqueness if generated
        $originalKey = $key;
        $counter = 1;
        while (ProductSortOption::where('key', $key)->exists()) {
            $key = $originalKey.'_'.$counter++;
        }

        $displayOrder = $validated['display_order'] ?? ((ProductSortOption::max('display_order') ?? 0) + 1);
        $isActive = $request->boolean('is_active', true);
        $isDefault = $request->boolean('is_default', false);

        if ($isDefault) {
            ProductSortOption::where('is_default', true)->update(['is_default' => false]);
            $isActive = true;
        }

        $sortOption = ProductSortOption::create([
            'label' => trim($validated['label']),
            'key' => $key,
            'sort_field' => trim($validated['sort_field']),
            'sort_direction' => strtolower($validated['sort_direction']),
            'display_order' => (int) $displayOrder,
            'is_active' => $isActive,
            'is_default' => $isDefault,
            'is_system' => false,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Sort option '{$sortOption->label}' created successfully.",
                'sort_option' => $sortOption,
            ]);
        }

        return redirect()->route('admin.sort-options.index')
            ->with('status', "Sort option '{$sortOption->label}' created successfully.");
    }

    /**
     * Update the specified sort option.
     */
    public function update(Request $request, ProductSortOption $sortOption): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'key' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('product_sort_options', 'key')->ignore($sortOption->id),
            ],
            'sort_field' => ['required', 'string', 'max:50'],
            'sort_direction' => ['required', 'string', 'in:asc,desc'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $isDefault = $request->boolean('is_default', false);
        $isActive = $request->boolean('is_active', true);

        if ($isDefault) {
            ProductSortOption::where('id', '!=', $sortOption->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
            $isActive = true;
        }

        $sortOption->update([
            'label' => trim($validated['label']),
            'key' => Str::slug($validated['key'], '_'),
            'sort_field' => trim($validated['sort_field']),
            'sort_direction' => strtolower($validated['sort_direction']),
            'display_order' => (int) ($validated['display_order'] ?? $sortOption->display_order),
            'is_active' => $isActive,
            'is_default' => $isDefault,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Sort option '{$sortOption->label}' updated successfully.",
                'sort_option' => $sortOption,
            ]);
        }

        return redirect()->route('admin.sort-options.index')
            ->with('status', "Sort option '{$sortOption->label}' updated successfully.");
    }

    /**
     * Toggle active state of a sort option.
     */
    public function toggle(Request $request, ProductSortOption $sortOption): JsonResponse|RedirectResponse
    {
        $newStatus = ! $sortOption->is_active;

        // If disabling the default option, unset default or ensure another default is set
        $wasDefault = $sortOption->is_default;
        if (! $newStatus && $wasDefault) {
            $sortOption->is_default = false;
            // Optionally promote another active option to default
            $nextDefault = ProductSortOption::where('id', '!=', $sortOption->id)->where('is_active', true)->first();
            if ($nextDefault) {
                $nextDefault->update(['is_default' => true]);
            }
        }

        $sortOption->is_active = $newStatus;
        $sortOption->save();

        $actionText = $sortOption->is_active ? 'enabled' : 'disabled';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $sortOption->is_active,
                'message' => "Sort option '{$sortOption->label}' {$actionText}.",
            ]);
        }

        return redirect()->route('admin.sort-options.index')
            ->with('status', "Sort option '{$sortOption->label}' {$actionText}.");
    }

    /**
     * Set the specified sort option as default.
     */
    public function setDefault(Request $request, ProductSortOption $sortOption): JsonResponse|RedirectResponse
    {
        ProductSortOption::where('is_default', true)->update(['is_default' => false]);

        $sortOption->update([
            'is_default' => true,
            'is_active' => true,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "'{$sortOption->label}' set as default sort option.",
            ]);
        }

        return redirect()->route('admin.sort-options.index')
            ->with('status', "'{$sortOption->label}' set as default sort option.");
    }

    /**
     * Reorder sort options.
     */
    public function reorder(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'orders' => ['required', 'array'],
            'orders.*.id' => ['required', 'integer', 'exists:product_sort_options,id'],
            'orders.*.display_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        foreach ($validated['orders'] as $item) {
            ProductSortOption::where('id', $item['id'])->update([
                'display_order' => (int) $item['display_order'],
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Sort options reordered successfully.',
            ]);
        }

        return redirect()->route('admin.sort-options.index')
            ->with('status', 'Sort options reordered successfully.');
    }

    /**
     * Move a single sort option up or down.
     */
    public function move(Request $request, ProductSortOption $sortOption): RedirectResponse
    {
        $direction = $request->input('direction', 'up');

        $options = ProductSortOption::orderBy('display_order', 'asc')->orderBy('id', 'asc')->get();
        $currentIndex = $options->search(fn ($item) => $item->id === $sortOption->id);

        if ($currentIndex === false) {
            return redirect()->route('admin.sort-options.index');
        }

        if ($direction === 'up' && $currentIndex > 0) {
            $prevOption = $options[$currentIndex - 1];
            $tempOrder = $prevOption->display_order;
            $prevOption->update(['display_order' => $sortOption->display_order]);
            $sortOption->update(['display_order' => $tempOrder]);
        } elseif ($direction === 'down' && $currentIndex < $options->count() - 1) {
            $nextOption = $options[$currentIndex + 1];
            $tempOrder = $nextOption->display_order;
            $nextOption->update(['display_order' => $sortOption->display_order]);
            $sortOption->update(['display_order' => $tempOrder]);
        }

        return redirect()->route('admin.sort-options.index')
            ->with('status', "Order for '{$sortOption->label}' updated.");
    }

    /**
     * Reset sort options to system presets.
     */
    public function reset(): RedirectResponse
    {
        ProductSortOption::seedDefaults();

        return redirect()->route('admin.sort-options.index')
            ->with('status', 'Sort options reset to defaults successfully.');
    }

    /**
     * Remove the specified sort option.
     */
    public function destroy(Request $request, ProductSortOption $sortOption): JsonResponse|RedirectResponse
    {
        $label = $sortOption->label;
        $wasDefault = $sortOption->is_default;

        $sortOption->delete();

        // If the deleted option was default, set the first active option as default
        if ($wasDefault) {
            $newDefault = ProductSortOption::where('is_active', true)->orderBy('display_order', 'asc')->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Sort option '{$label}' removed successfully.",
            ]);
        }

        return redirect()->route('admin.sort-options.index')
            ->with('status', "Sort option '{$label}' removed successfully.");
    }
}
