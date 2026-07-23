<?php

namespace App\Actions;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteProductAction
{
    public function handle(Product $product): void
    {
        $imagePath = $product->uploadedImageStoragePath();

        DB::transaction(fn () => $product->delete());

        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
