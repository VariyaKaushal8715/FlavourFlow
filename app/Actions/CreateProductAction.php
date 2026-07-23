<?php

namespace App\Actions;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateProductAction
{
    /**
     * @param  array<string, mixed>  $productData
     */
    public function handle(array $productData): Product
    {
        return DB::transaction(function () use ($productData): Product {
            /** @var UploadedFile|null $image */
            $image = Arr::pull($productData, 'image');

            if ($image instanceof UploadedFile) {
                $productData['image_path'] = 'storage/'.$image->store('products', 'public');
            }

            if ($productData['is_featured']) {
                Product::query()->where('is_featured', true)->update(['is_featured' => false]);
            }

            $productData['slug'] = $this->uniqueSlug($productData['name']);

            return Product::query()->create($productData);
        });
    }

    private function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 2;

        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
