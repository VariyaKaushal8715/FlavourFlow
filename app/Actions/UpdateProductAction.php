<?php

namespace App\Actions;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class UpdateProductAction
{
    /**
     * @param  array<string, mixed>  $productData
     */
    public function handle(Product $product, array $productData): Product
    {
        /** @var UploadedFile|null $image */
        $image = Arr::pull($productData, 'image');
        $previousImagePath = $product->uploadedImageStoragePath();
        $newImagePath = null;

        if ($image instanceof UploadedFile) {
            $newImagePath = $image->store('products', 'public');
            $productData['image_path'] = 'storage/'.$newImagePath;
        }

        try {
            DB::transaction(function () use ($product, $productData): void {
                if ($productData['is_featured']) {
                    Product::query()
                        ->whereKeyNot($product->getKey())
                        ->where('is_featured', true)
                        ->update(['is_featured' => false]);
                }

                if ($product->name !== $productData['name']) {
                    $productData['slug'] = $this->uniqueSlug($productData['name'], $product);
                }

                $product->update($productData);
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

        return $product->refresh();
    }

    private function uniqueSlug(string $name, Product $product): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 2;

        while (Product::query()->whereKeyNot($product->getKey())->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
