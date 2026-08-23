<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdminCategoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('access-admin');

        $categories = Product::query()
            ->select(
                'products.category',
                DB::raw('COUNT(DISTINCT products.id) as total_products'),
                DB::raw('SUM(CASE WHEN products.is_active = 1 THEN 1 ELSE 0 END) as active_products'),
                DB::raw('SUM(products.quantity) as total_stock'),
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as units_sold'),
                DB::raw('COALESCE(SUM(order_items.total_price), 0) as total_revenue')
            )
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function ($join): void {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->where('orders.status', '=', 'completed');
            })
            ->groupBy('products.category')
            ->orderByDesc('total_revenue')
            ->get();

        $stats = [
            'total_categories' => $categories->count(),
            'total_products' => Product::count(),
            'total_revenue' => $categories->sum('total_revenue'),
            'total_units_sold' => $categories->sum('units_sold'),
        ];

        return view('admin.categories.index', [
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }

    public function show(string $category): View
    {
        Gate::authorize('access-admin');

        $products = Product::query()
            ->select(
                'products.*',
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as units_sold'),
                DB::raw('COALESCE(SUM(order_items.total_price), 0) as revenue_generated')
            )
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function ($join): void {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->where('orders.status', '=', 'completed');
            })
            ->where('products.category', $category)
            ->groupBy(
                'products.id',
                'products.name',
                'products.slug',
                'products.sku',
                'products.category',
                'products.unit',
                'products.description',
                'products.long_description',
                'products.highlights',
                'products.ingredients',
                'products.usage_instructions',
                'products.origin',
                'products.badge',
                'products.price',
                'products.compare_at_price',
                'products.quantity',
                'products.low_stock_threshold',
                'products.rating',
                'products.priority',
                'products.image_path',
                'products.is_featured',
                'products.is_active',
                'products.created_at',
                'products.updated_at'
            )
            ->orderByDesc('revenue_generated')
            ->paginate(15);

        $stats = [
            'category_name' => $category,
            'total_products' => Product::where('category', $category)->count(),
            'active_products' => Product::where('category', $category)->where('is_active', true)->count(),
            'total_stock' => (int) Product::where('category', $category)->sum('quantity'),
            'out_of_stock' => Product::where('category', $category)->where('quantity', 0)->count(),
            'low_stock' => Product::where('category', $category)->lowStock()->count(),
        ];

        return view('admin.categories.show', [
            'category' => $category,
            'products' => $products,
            'stats' => $stats,
        ]);
    }
}

