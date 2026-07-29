<?php

namespace App\Actions;

use App\Models\Offer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UpdateOfferAction
{
    /**
     * @param  array<string, mixed>  $offerData
     */
    public function handle(Offer $offer, array $offerData): Offer
    {
        /** @var UploadedFile|null $image */
        $image = Arr::pull($offerData, 'image');
        $previousImagePath = $offer->uploadedImageStoragePath();
        $newImagePath = null;

        if ($image instanceof UploadedFile) {
            $newImagePath = $image->store('offers', 'public');
            $offerData['image_path'] = 'storage/'.$newImagePath;
        }

        try {
            DB::transaction(function () use ($offer, $offerData): void {
                if ($offerData['is_featured']) {
                    Offer::query()
                        ->whereKeyNot($offer->getKey())
                        ->where('is_featured', true)
                        ->update(['is_featured' => false]);
                }

                $offer->update($offerData);
            });
        } catch (Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $exception;
        }

        if ($newImagePath && $previousImagePath) {
            Storage::disk('public')->delete($previousImagePath);
        }

        return $offer->refresh();
    }
}
