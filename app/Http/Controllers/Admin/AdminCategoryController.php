<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdminCategoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('access-admin');

        $salesSubquery = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '=', 'completed')
            ->select('order_items.product_id')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as units_sold')
            ->selectRaw('COALESCE(SUM(order_items.total_price), 0) as total_revenue')
            ->groupBy('order_items.product_id');

        $categories = Product::query()
            ->select(
                'products.category',
                DB::raw('COUNT(products.id) as total_products'),
                DB::raw('SUM(CASE WHEN products.is_active = 1 THEN 1 ELSE 0 END) as active_products'),
                DB::raw('SUM(products.quantity) as total_stock'),
                DB::raw('COALESCE(SUM(sales.units_sold), 0) as units_sold'),
                DB::raw('COALESCE(SUM(sales.total_revenue), 0) as total_revenue')
            )
            ->leftJoinSub($salesSubquery, 'sales', function ($join): void {
                $join->on('products.id', '=', 'sales.product_id');
            })
            ->whereNotNull('products.category')
            ->where('products.category', '!=', '')
            ->groupBy('products.category')
            ->orderByDesc('total_revenue')
            ->get();

        $stats = [
            'total_categories' => $categories->count(),
            'total_products' => Product::count(),
            'total_revenue' => (float) $categories->sum('total_revenue'),
            'total_units_sold' => (int) $categories->sum('units_sold'),
        ];

        return view('admin.categories.index', [
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }

    public function show(string $category): View
    {
        Gate::authorize('access-admin');

        $salesSubquery = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '=', 'completed')
            ->select('order_items.product_id')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as units_sold')
            ->selectRaw('COALESCE(SUM(order_items.total_price), 0) as revenue_generated')
            ->groupBy('order_items.product_id');

        $products = Product::query()
            ->select('products.*')
            ->selectRaw('COALESCE(sales.units_sold, 0) as units_sold')
            ->selectRaw('COALESCE(sales.revenue_generated, 0) as revenue_generated')
            ->leftJoinSub($salesSubquery, 'sales', function ($join): void {
                $join->on('products.id', '=', 'sales.product_id');
            })
            ->where('products.category', $category)
            ->orderByDesc('revenue_generated')
            ->orderByDesc('units_sold')
            ->orderBy('products.name')
            ->paginate(15)
            ->withQueryString();

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
