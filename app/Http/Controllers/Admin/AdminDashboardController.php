<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        if (! $request->user()) {
            return view('admin.login');
        }

        Gate::authorize('access-admin');

        /** @var object{total:int,active:int,out_of_stock:int,low_stock:int} */
        $inventory = Product::query()
            ->toBase()
            ->selectRaw('count(*) as total')
            ->selectRaw('count(case when is_active = 1 then 1 end) as active')
            ->selectRaw('count(case when quantity = 0 then 1 end) as out_of_stock')
            ->selectRaw('count(case when quantity > 0 and quantity <= low_stock_threshold then 1 end) as low_stock')
            ->first();

        // Orders & revenue metrics
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        $totalRevenue = (float) Order::where('status', 'completed')->sum('total_amount');
        $averageOrderValue = $completedOrders > 0 ? ($totalRevenue / $completedOrders) : 0;

        // Today vs yesterday
        $todayRevenue = (float) Order::where('status', 'completed')->whereDate('created_at', Carbon::today())->sum('total_amount');
        $yesterdayRevenue = (float) Order::where('status', 'completed')->whereDate('created_at', Carbon::yesterday())->sum('total_amount');
        $revenueGrowth = $yesterdayRevenue > 0 ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100) : null;

        $todayOrders = Order::whereDate('created_at', Carbon::today())->count();
        $yesterdayOrders = Order::whereDate('created_at', Carbon::yesterday())->count();
        $ordersGrowth = $yesterdayOrders > 0 ? round((($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100) : null;

        // Customers
        $customersCount = User::where('is_admin', false)->count();
        $newCustomersThisWeek = User::where('is_admin', false)->where('created_at', '>=', Carbon::now()->startOfWeek())->count();
        $wishlistCount = WishlistItem::count();

        // Recent orders with user
        $recentOrders = Order::with('user')->latest()->take(6)->get();

        // Top selling products (by completed order items)
        $topProducts = Product::select(
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
            DB::raw('COALESCE(SUM(order_items.quantity), 0) as units_sold'),
            DB::raw('COALESCE(SUM(order_items.total_price), 0) as revenue_generated')
        )
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
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
                'products.rating'
            )
            ->orderByDesc('units_sold')
            ->take(5)
            ->get();

        // Best categories with revenue & count
        $bestCategories = Product::query()
            ->select(
                'products.category',
                DB::raw('COUNT(DISTINCT products.id) as count'),
                DB::raw('COALESCE(SUM(order_items.total_price), 0) as category_revenue'),
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as category_units')
            )
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function ($join): void {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->where('orders.status', '=', 'completed');
            })
            ->groupBy('products.category')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        $maxCategoryCount = $bestCategories->max('count') ?: 1;

        // Low stock and out of stock alerts
        $inventoryAlerts = Product::where(function ($q) {
            $q->where('quantity', 0)
                ->orWhereColumn('quantity', '<=', 'low_stock_threshold');
        })
            ->orderBy('quantity')
            ->take(6)
            ->get();

        // Chart Data (7 Days)
        $chartData7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);
            $revenue = (float) Order::where('status', 'completed')->whereDate('created_at', $date)->sum('total_amount');
            $orders = Order::whereDate('created_at', $date)->count();

            return [
                'date' => $date->format('M d'),
                'revenue' => $revenue,
                'orders' => $orders,
            ];
        });

        // Chart Data (30 Days)
        $chartData30Days = collect(range(29, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);
            $revenue = (float) Order::where('status', 'completed')->whereDate('created_at', $date)->sum('total_amount');
            $orders = Order::whereDate('created_at', $date)->count();

            return [
                'date' => $date->format('M d'),
                'revenue' => $revenue,
                'orders' => $orders,
            ];
        });

        return view('admin.dashboard', compact(
            'inventory',
            'totalOrders',
            'pendingOrders',
            'processingOrders',
            'completedOrders',
            'cancelledOrders',
            'totalRevenue',
            'averageOrderValue',
            'todayRevenue',
            'todayOrders',
            'revenueGrowth',
            'ordersGrowth',
            'customersCount',
            'newCustomersThisWeek',
            'wishlistCount',
            'recentOrders',
            'topProducts',
            'bestCategories',
            'maxCategoryCount',
            'inventoryAlerts',
            'chartData7Days',
            'chartData30Days',
        ));
    }
}
