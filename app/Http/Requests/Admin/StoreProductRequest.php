<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('access-admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'sku' => ['required', 'string', 'max:60', Rule::unique(Product::class, 'sku')],
            'category' => ['required', 'string', 'max:80'],
            'unit' => ['required', 'string', 'max:30'],
            'description' => ['required', 'string', 'max:500'],
            'long_description' => ['nullable', 'string', 'max:3000'],
            'highlights' => ['nullable', 'array', 'max:6'],
            'highlights.*' => ['string', 'max:120'],
            'ingredients' => ['nullable', 'string', 'max:1000'],
            'usage_instructions' => ['nullable', 'string', 'max:1000'],
            'origin' => ['nullable', 'string', 'max:120'],
            'badge' => ['required', 'string', 'max:40'],
            'price' => ['required', 'decimal:0,2', 'min:0', 'max:999999.99'],
            'compare_at_price' => ['nullable', 'decimal:0,2', 'gt:price', 'max:999999.99'],
            'quantity' => ['required', 'integer', 'between:0,1000000'],
            'low_stock_threshold' => ['required', 'integer', 'between:0,1000000'],
            'rating' => ['required', 'numeric', 'between:0,5'],
            'priority' => ['required', 'integer', 'between:0,100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_featured' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'delivery_mode' => ['required', 'string', 'in:all,specific,custom'],
            'deliverable_locations' => ['nullable', 'array'],
            'deliverable_locations.*.country' => ['required_with:deliverable_locations', 'string', 'max:100'],
            'deliverable_locations.*.state' => ['nullable', 'string', 'max:100'],
            'deliverable_locations.*.city' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $deliveryMode = in_array($this->input('delivery_mode'), ['all', 'specific', 'custom'], true)
            ? ($this->input('delivery_mode') === 'custom' ? 'specific' : $this->input('delivery_mode'))
            : 'all';

        $locations = $this->normaliseDeliverableLocations($this->input('deliverable_locations'), $deliveryMode);

        $this->merge([
            'sku' => $this->string('sku')->trim()->upper()->toString(),
            'highlights' => $this->normaliseHighlights($this->input('highlights')),
            'is_featured' => $this->boolean('is_featured'),
            'is_active' => $this->boolean('is_active'),
            'delivery_mode' => $deliveryMode,
            'deliverable_locations' => $locations,
        ]);
    }

    /**
     * @return array<int, array{country: string, state: ?string, city: ?string}>|null
     */
    private function normaliseDeliverableLocations(mixed $locations, string $mode): ?array
    {
        if ($mode !== 'specific' || ! is_array($locations)) {
            return null;
        }

        $cleaned = [];
        foreach ($locations as $loc) {
            if (! is_array($loc)) {
                continue;
            }

            $country = trim((string) ($loc['country'] ?? ''));
            if ($country === '') {
                continue;
            }

            $isIndia = strtolower($country) === 'india';
            $state = $isIndia ? trim((string) ($loc['state'] ?? '')) : null;
            $city = $isIndia ? trim((string) ($loc['city'] ?? '')) : null;

            $cleaned[] = [
                'country' => $country,
                'state' => $state ?: null,
                'city' => $city ?: null,
            ];
        }

        return ! empty($cleaned) ? $cleaned : null;
    }

    /**
     * @return array<int, string>|null
     */
    private function normaliseHighlights(mixed $highlights): ?array
    {
        if (is_array($highlights)) {
            return array_values(array_filter(array_map('trim', $highlights)));
        }

        if (! is_string($highlights) || trim($highlights) === '') {
            return null;
        }

        return array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', $highlights) ?: [],
        )));
    }
}
