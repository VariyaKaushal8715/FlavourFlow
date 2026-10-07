<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\GujaratLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LocationController extends Controller
{
    public function autocomplete(Request $request): JsonResponse
    {
        $query = $request->string('q')->trim()->lower()->toString();

        $cities = collect(GujaratLocation::cities())
            ->filter(fn (string $city): bool => $query === '' || Str::of($city)->lower()->contains($query))
            ->values()
            ->all();

        return response()->json([
            'cities' => $cities,
        ]);
    }
}
