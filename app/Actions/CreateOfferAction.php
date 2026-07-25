<?php

namespace App\Actions;

use App\Models\Offer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateOfferAction
{
    /**
     * @param  array<string, mixed>  $offerData
     */
    public function handle(array $offerData): Offer
    {
        return DB::transaction(function () use ($offerData): Offer {
            /** @var UploadedFile|null $image */
            $image = Arr::pull($offerData, 'image');

            if ($image instanceof UploadedFile) {
                $offerData['image_path'] = 'storage/'.$image->store('offers', 'public');
            }

            if ($offerData['is_featured']) {
                Offer::query()->where('is_featured', true)->update(['is_featured' => false]);
            }

            return Offer::query()->create($offerData);
        });
    }
}
