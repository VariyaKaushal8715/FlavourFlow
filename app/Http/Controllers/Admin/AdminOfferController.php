<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateOfferAction;
use App\Actions\DeleteOfferAction;
use App\Actions\UpdateOfferAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOfferRequest;
use App\Http\Requests\Admin\UpdateOfferRequest;
use App\Models\Offer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminOfferController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $offers = Offer::query()
            ->search($search)
            ->orderByDesc('is_featured')
            ->orderByDesc('priority')
            ->latest()
            ->paginate(10)
            ->withQueryString();
        $now = now();
        $offerStats = Offer::query()
            ->toBase()
            ->selectRaw('count(*) as total')
            ->selectRaw(
                'count(case when is_active = 1 and (starts_at is null or starts_at <= ?) and (ends_at is null or ends_at >= ?) then 1 end) as live',
                [$now, $now],
            )
            ->selectRaw('count(case when is_featured = 1 then 1 end) as featured')
            ->first();

        return view('admin.offers.index', [
            'offers' => $offers,
            'search' => $search,
            'offerStats' => $offerStats,
        ]);
    }

    public function store(
        StoreOfferRequest $request,
        CreateOfferAction $createOffer,
    ): RedirectResponse {
        $offer = $createOffer->handle($request->validated());

        return redirect()
            ->route('admin.offers.index')
            ->with('status', "{$offer->title} was added.");
    }

    public function edit(Offer $offer): View
    {
        Gate::authorize('access-admin');

        return view('admin.offers.edit', ['offer' => $offer]);
    }

    public function update(
        UpdateOfferRequest $request,
        Offer $offer,
        UpdateOfferAction $updateOffer,
    ): RedirectResponse {
        $offer = $updateOffer->handle($offer, $request->validated());

        return redirect()
            ->route('admin.offers.index')
            ->with('status', "{$offer->title} was updated.");
    }

    public function destroy(
        Offer $offer,
        DeleteOfferAction $deleteOffer,
    ): RedirectResponse {
        Gate::authorize('access-admin');

        $offerTitle = $offer->title;
        $deleteOffer->handle($offer);

        return redirect()
            ->route('admin.offers.index')
            ->with('status', "{$offerTitle} was permanently deleted.");
    }
}
