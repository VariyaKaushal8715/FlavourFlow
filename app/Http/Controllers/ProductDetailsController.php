<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class ProductDetailsController extends Controller
{
    public function __invoke(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $relatedProducts = Product::query()
            ->active()
            ->whereKeyNot($product->getKey())
            ->where('category', $product->category)
            ->orderByDesc('priority')
            ->limit(3)
            ->get()
            ->map->toHighlightData()
            ->all();

        return view('products.show', [
            'site' => config('personal_site'),
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
