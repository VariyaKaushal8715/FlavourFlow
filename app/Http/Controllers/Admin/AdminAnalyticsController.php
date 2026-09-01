<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdminAnalyticsController extends Controller
{
    public function sales(Request $request): View
    {
        Gate::authorize('access-admin');

        $search = $request->string('search')->trim()->limit(100)->toString();
        $category = $request->string('category')->trim()->toString();
        $timeframe = $request->string('timeframe', '30')->trim()->toString();

        $days = match ($timeframe) {
            '7' => 7,
            'today' => 1,
            '90' => 90,
            default => 30,
        };

        $startDate = $timeframe === 'today'
            ? Carbon::today()
            : Carbon::now()->subDays($days)->startOfDay();

        $totalStoreRevenue = (float) Order::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->sum('total_amount');

        $totalStoreOrders = Order::where('created_at', '>=', $startDate)->count();

        $productsQuery = Product::query()
            ->select(
                'products.id',
                'products.name',
                'products.slug',
                'products.sku',
                'products.category',
                'products.price',
                'products.quantity',
                'products.low_stock_threshold',
                'products.image_path',
                'products.rating',
                'products.is_active',
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as units_sold'),
                DB::raw('COALESCE(SUM(order_items.total_price), 0) as revenue_generated'),
                DB::raw('COUNT(DISTINCT orders.id) as orders_count')
            )
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function ($join) use ($startDate): void {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->where('orders.status', '=', 'completed')
                    ->where('orders.created_at', '>=', $startDate);
            })
            ->groupBy(
                'products.id',
                'products.name',
                'products.slug',
                'products.sku',
                'products.category',
                'products.price',
                'products.quantity',
                'products.low_stock_threshold',
                'products.image_path',
                'products.rating',
                'products.is_active'
            );

        if ($search !== '') {
            $productsQuery->where(function ($q) use ($search): void {
                $q->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.sku', 'like', "%{$search}%");
            });
        }

        if ($category !== '' && $category !== 'all') {
            $productsQuery->where('products.category', $category);
        }

        $products = $productsQuery
            ->orderByDesc('revenue_generated')
            ->orderByDesc('units_sold')
            ->paginate(15)
            ->withQueryString();

        $categories = Product::query()
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        // Trend data for chart over selected timeframe
        $chartPoints = min($days, 30);
        $trendData = collect(range($chartPoints - 1, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);
            $revenue = (float) Order::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('total_amount');
            $units = (int) OrderItem::whereHas('order', function ($q) use ($date): void {
                $q->where('status', 'completed')->whereDate('created_at', $date);
            })->sum('quantity');

            return [
                'date' => $date->format('M d'),
                'revenue' => $revenue,
                'units' => $units,
            ];
        });

        return view('admin.analytics.sales', [
            'products' => $products,
            'categories' => $categories,
            'totalStoreRevenue' => $totalStoreRevenue,
            'totalStoreOrders' => $totalStoreOrders,
            'trendData' => $trendData,
            'filters' => [
                'search' => $search,
                'category' => $category ?: 'all',
                'timeframe' => $timeframe,
            ],
        ]);
    }

    public function product(Product $product): View
    {
        Gate::authorize('access-admin');

        $completedOrderItems = OrderItem::where('product_id', $product->id)
            ->whereHas('order', function ($q): void {
                $q->where('status', 'completed');
            });

        $totalUnitsSold = (int) $completedOrderItems->sum('quantity');
        $totalRevenue = (float) $completedOrderItems->sum('total_price');
        $totalOrdersCount = Order::whereHas('items', function ($q) use ($product): void {
            $q->where('product_id', $product->id);
        })->count();

        // 14-day sales trend for this product
        $productTrend = collect(range(13, 0))->map(function ($daysAgo) use ($product) {
            $date = Carbon::today()->subDays($daysAgo);
            $units = (int) OrderItem::where('product_id', $product->id)
                ->whereHas('order', function ($q) use ($date): void {
                    $q->where('status', 'completed')->whereDate('created_at', $date);
                })
                ->sum('quantity');

            $revenue = (float) OrderItem::where('product_id', $product->id)
                ->whereHas('order', function ($q) use ($date): void {
                    $q->where('status', 'completed')->whereDate('created_at', $date);
                })
                ->sum('total_price');

            return [
                'date' => $date->format('M d'),
                'units' => $units,
                'revenue' => $revenue,
            ];
        });

        // Recent orders containing this product
        $recentOrders = Order::query()
            ->with(['user', 'items' => function ($q) use ($product): void {
                $q->where('product_id', $product->id);
            }])
            ->whereHas('items', function ($q) use ($product): void {
                $q->where('product_id', $product->id);
            })
            ->latest()
            ->take(10)
            ->get();

        return view('admin.analytics.product', [
            'product' => $product,
            'totalUnitsSold' => $totalUnitsSold,
            'totalRevenue' => $totalRevenue,
            'totalOrdersCount' => $totalOrdersCount,
            'productTrend' => $productTrend,
            'recentOrders' => $recentOrders,
        ]);
    }
}
