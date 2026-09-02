<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminInventoryController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('access-admin');

        $search = $request->string('search')->trim()->limit(100)->toString();
        $status = $request->string('status')->trim()->toString();
        $category = $request->string('category')->trim()->toString();
        $sort = $request->string('sort', 'stock_asc')->trim()->toString();

        $query = Product::query()->search($search);

        if ($status === 'out_of_stock') {
            $query->where('quantity', 0);
        } elseif ($status === 'low_stock') {
            $query->lowStock();
        } elseif ($status === 'healthy') {
            $query->where('quantity', '>', 0)
                ->whereColumn('quantity', '>', 'low_stock_threshold');
        }

        if ($category !== '' && $category !== 'all') {
            $query->where('category', $category);
        }

        match ($sort) {
            'stock_desc' => $query->orderByDesc('quantity'),
            'price_high' => $query->orderByDesc('price'),
            'price_low' => $query->orderBy('price'),
            'name_asc' => $query->orderBy('name'),
            default => $query->orderBy('quantity'),
        };

        $products = $query->paginate(15)->withQueryString();

        $stats = [
            'total_items' => Product::count(),
            'total_units' => (int) Product::sum('quantity'),
            'out_of_stock' => Product::where('quantity', 0)->count(),
            'low_stock' => Product::query()->lowStock()->count(),
            'healthy' => Product::where('quantity', '>', 0)
                ->whereColumn('quantity', '>', 'low_stock_threshold')
                ->count(),
        ];

        $categories = Product::query()
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return view('admin.inventory.index', [
            'products' => $products,
            'stats' => $stats,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'status' => $status ?: 'all',
                'category' => $category ?: 'all',
                'sort' => $sort,
            ],
        ]);
    }
}
