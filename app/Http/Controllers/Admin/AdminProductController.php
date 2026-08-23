<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateProductAction;
use App\Actions\DeleteProductAction;
use App\Actions\UpdateProductAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminProductController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('access-admin');

        $search = $request->string('search')->trim()->limit(100)->toString();
        $status = $this->validStatus($request->string('status')->toString());
        $sort = $this->validSort($request->string('sort')->toString());
        $productsQuery = Product::query()->search($search);

        $this->applyStatus($productsQuery, $status);
        $this->applySort($productsQuery, $sort);

        return view('admin.products.index', [
            'products' => $productsQuery->paginate(10)->withQueryString(),
            'inventory' => Product::query()
                ->toBase()
                ->selectRaw('count(*) as total')
                ->selectRaw('count(case when is_active = 1 then 1 end) as active')
                ->selectRaw('count(case when quantity = 0 then 1 end) as out_of_stock')
                ->selectRaw('count(case when quantity > 0 and quantity <= low_stock_threshold then 1 end) as low_stock')
                ->first(),
            'filters' => compact('search', 'status', 'sort'),
        ]);
    }

    public function store(
        StoreProductRequest $request,
        CreateProductAction $createProduct,
    ): RedirectResponse {
        $product = $createProduct->handle($request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('status', "{$product->name} was added and is ready for the homepage.");
    }

    public function edit(Product $product): View
    {
        Gate::authorize('access-admin');

        return view('admin.edit-product', ['product' => $product]);
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        UpdateProductAction $updateProduct,
    ): RedirectResponse {
        $product = $updateProduct->handle($product, $request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('status', "{$product->name} was updated.");
    }

    public function destroy(
        Product $product,
        DeleteProductAction $deleteProduct,
    ): RedirectResponse {
        Gate::authorize('access-admin');

        $productName = $product->name;
        $deleteProduct->handle($product);

        return redirect()
            ->route('admin.products.index')
            ->with('status', "{$productName} was permanently deleted.");
    }

    private function validStatus(string $status): string
    {
        return in_array($status, ['active', 'inactive', 'low_stock', 'out_of_stock'], true)
            ? $status
            : 'all';
    }

    private function validSort(string $sort): string
    {
        return in_array($sort, ['oldest', 'price_high', 'price_low', 'stock_low', 'priority'], true)
            ? $sort
            : 'newest';
    }

    private function applyStatus(Builder $query, string $status): void
    {
        match ($status) {
            'active' => $query->where('is_active', true),
            'inactive' => $query->where('is_active', false),
            'low_stock' => $query->lowStock(),
            'out_of_stock' => $query->where('quantity', 0),
            default => null,
        };
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'oldest' => $query->oldest(),
            'price_high' => $query->orderByDesc('price'),
            'price_low' => $query->orderBy('price'),
            'stock_low' => $query->orderBy('quantity'),
            'priority' => $query->orderByDesc('priority'),
            default => $query->latest(),
        };
    }
}
