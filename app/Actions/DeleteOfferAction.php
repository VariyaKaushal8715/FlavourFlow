<?php

namespace App\Actions;

use App\Models\Offer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteOfferAction
{
    public function handle(Offer $offer): void
    {
        $imagePath = $offer->uploadedImageStoragePath();

        DB::transaction(fn () => $offer->delete());

        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
