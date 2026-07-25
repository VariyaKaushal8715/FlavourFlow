<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Product;
use App\Support\ProductHighlightBuilder;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __construct(private ProductHighlightBuilder $productHighlightBuilder) {}

    public function __invoke(): View
    {
        $site = config('personal_site');
        $storedProducts = Product::query()
            ->active()
            ->orderByDesc('is_featured')
            ->orderByDesc('priority')
            ->latest()
            ->get();
        $products = $storedProducts->isNotEmpty()
            ? $storedProducts->map->toHighlightData()->all()
            : $site['products'];

        $site['hero']['product_showcase'] = $this->productHighlightBuilder->forHero($products);
        $offers = Offer::query()
            ->visibleNow()
            ->orderByDesc('is_featured')
            ->orderByDesc('priority')
            ->limit(6)
            ->get();

        return view('welcome', [
            'site' => $site,
            'products' => $products,
            'offers' => $offers,
        ]);
    }
}
