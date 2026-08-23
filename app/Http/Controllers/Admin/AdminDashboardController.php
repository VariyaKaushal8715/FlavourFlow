<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        if (! $request->user()) {
            return view('admin.login');
        }

        Gate::authorize('access-admin');

        $inventory = Product::query()
            ->toBase()
            ->selectRaw('count(*) as total')
            ->selectRaw('count(case when is_active = 1 then 1 end) as active')
            ->selectRaw('count(case when quantity = 0 then 1 end) as out_of_stock')
            ->selectRaw('count(case when quantity > 0 and quantity <= low_stock_threshold then 1 end) as low_stock')
            ->first();

        $customersCount = User::where('is_admin', false)->count();
        $wishlistCount = WishlistItem::count();

        return view('admin.dashboard', [
            'inventory' => $inventory,
            'customersCount' => $customersCount,
            'wishlistCount' => $wishlistCount,
            // Placeholders since orders table doesn't exist yet
            'totalOrders' => 0,
            'pendingOrders' => 0,
            'completedOrders' => 0,
            'totalRevenue' => 0,
            'todayRevenue' => 0,
            'todayOrders' => 0,
            'recentOrders' => collect(),
            'topProducts' => Product::take(5)->get(),
            'bestCategories' => Product::selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->orderByDesc('count')
                ->take(4)
                ->get(),
        ]);
    }
}
