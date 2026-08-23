<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

        // Customers & engagement (real)
        $wishlistCount = WishlistItem::count();

        // Low stock and out of stock alerts (real)
        $inventoryAlerts = Product::where(function ($q) {
            $q->where('quantity', 0)
                ->orWhereColumn('quantity', '<=', 'low_stock_threshold');
        })
            ->orderBy('quantity')
            ->take(6)
            ->get();

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

        /*
        |----------------------------------------------------------------------
        | Dashboard KPI Metrics — Realistic Dummy Data
        |----------------------------------------------------------------------
        | Structured as plain scalars so swapping to real DB queries later is
        | a one-line change per metric. Replace each value with its Eloquent
        | equivalent when the orders pipeline is populated.
        */
        $totalOrders = 1284;
        $pendingOrders = 23;
        $processingOrders = 41;
        $completedOrders = 1189;
        $cancelledOrders = 31;
        $totalRevenue = 847520.0;
        $averageOrderValue = 713.0;
        $todayRevenue = 12480.0;
        $todayOrders = 18;
        $revenueGrowth = 12;   // percentage vs yesterday
        $ordersGrowth = 8;     // percentage vs yesterday
        $customersCount = 642;
        $newCustomersThisWeek = 27;

        /*
        |----------------------------------------------------------------------
        | Store Pulse — Multi-Metric Chart Data (Dummy)
        |----------------------------------------------------------------------
        | Each day entry contains every metric the chart can display. The view
        | picks the active metric's key from each entry. To switch to real data,
        | replace the arrays below with date-indexed DB aggregations.
        */
        $chartData7Days = $this->generateDummyChartData(7);
        $chartData30Days = $this->generateDummyChartData(30);

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

    /**
     * Generate realistic dummy chart data for the given number of past days.
     *
     * Each entry has keys matching every metric the Store Pulse chart can display.
     * Replace this method with real DB aggregations when order data is available.
     *
     * @return Collection<int, array{date: string, revenue: float, orders: int, sales: float, products_sold: int, customers: int, avg_order_value: float}>
     */
    private function generateDummyChartData(int $days): Collection
    {
        // Seed with deterministic but realistic-looking values
        $baseRevenue = 11000;
        $baseOrders = 16;
        $baseCustomers = 9;

        return collect(range($days - 1, 0))->map(function (int $daysAgo) use ($baseRevenue, $baseOrders, $baseCustomers) {
            $date = Carbon::today()->subDays($daysAgo);
            $dayOfWeek = $date->dayOfWeek;

            // Weekend uplift factor (Sat/Sun get ~30% more traffic)
            $weekendFactor = ($dayOfWeek === 0 || $dayOfWeek === 6) ? 1.3 : 1.0;

            // Create organic-looking variation using the day index
            $seed = ($daysAgo * 7 + 3) % 13;
            $jitter = 0.75 + ($seed / 13) * 0.55; // range 0.75–1.30

            $orders = max(3, (int) round($baseOrders * $jitter * $weekendFactor));
            $revenue = round($baseRevenue * $jitter * $weekendFactor, 2);
            $productsSold = max(4, (int) round($orders * (1.6 + ($seed % 5) * 0.15)));
            $customers = max(2, (int) round($baseCustomers * $jitter * $weekendFactor * 0.85));
            $avgOrderValue = $orders > 0 ? round($revenue / $orders, 2) : 0;
            $sales = $revenue; // Sales ≈ Revenue for a single-channel store
            $discounts = round($revenue * 0.05 * $jitter, 2); // roughly 5% discount
            $returns = max(0, (int) round($orders * 0.02 * $jitter)); // roughly 2% return rate

            return [
                'date' => $date->format('M d'),
                'revenue' => $revenue,
                'orders' => $orders,
                'sales' => $sales,
                'products_sold' => $productsSold,
                'customers' => $customers,
                'avg_order_value' => $avgOrderValue,
                'discounts' => $discounts,
                'returns' => $returns,
            ];
        });
    }
}
