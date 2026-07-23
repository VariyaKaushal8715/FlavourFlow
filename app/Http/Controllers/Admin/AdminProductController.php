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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class AdminProductController extends Controller
{
    public function store(
        StoreProductRequest $request,
        CreateProductAction $createProduct,
    ): RedirectResponse {
        $product = $createProduct->handle($request->validated());

        return redirect()
            ->route('admin.index')
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
            ->route('admin.index')
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
            ->route('admin.index')
            ->with('status', "{$productName} was permanently deleted.");
    }
}
