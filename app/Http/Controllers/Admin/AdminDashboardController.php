<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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

        $search = $request->string('search')->trim()->limit(100)->toString();
        $status = $this->validStatus($request->string('status')->toString());
        $sort = $this->validSort($request->string('sort')->toString());
        $productsQuery = Product::query()->search($search);

        $this->applyStatus($productsQuery, $status);
        $this->applySort($productsQuery, $sort);

        return view('admin.dashboard', [
            'products' => $productsQuery->paginate(10)->withQueryString(),
            'inventory' => Product::query()
                ->toBase()
                ->selectRaw('count(*) as total')
                ->selectRaw('count(case when is_active = 1 then 1 end) as active')
                ->selectRaw('count(case when quantity = 0 then 1 end) as out_of_stock')
                ->selectRaw('count(case when quantity > 0 and quantity <= low_stock_threshold then 1 end) as low_stock')
                ->first(),
            'filters' => compact('search', 'status', 'sort'),
        ]);
    }

    private function validStatus(string $status): string
    {
        return in_array($status, ['active', 'inactive', 'low_stock', 'out_of_stock'], true)
            ? $status
            : 'all';
    }

    private function validSort(string $sort): string
    {
        return in_array($sort, ['oldest', 'price_high', 'price_low', 'stock_low', 'priority'], true)
            ? $sort
            : 'newest';
    }

    private function applyStatus(Builder $query, string $status): void
    {
        match ($status) {
            'active' => $query->where('is_active', true),
            'inactive' => $query->where('is_active', false),
            'low_stock' => $query->lowStock(),
            'out_of_stock' => $query->where('quantity', 0),
            default => null,
        };
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'oldest' => $query->oldest(),
            'price_high' => $query->orderByDesc('price'),
            'price_low' => $query->orderBy('price'),
            'stock_low' => $query->orderBy('quantity'),
            'priority' => $query->orderByDesc('priority'),
            default => $query->latest(),
        };
    }
}
