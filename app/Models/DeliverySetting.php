<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeliverySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'mode',
        'custom_locations',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'custom_locations' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the active delivery setting or create default.
     */
    public static function current(): self
    {
        $setting = static::where('is_active', true)->latest()->first();

        if (! $setting) {
            $setting = static::create([
                'mode' => 'all',
                'custom_locations' => [],
                'is_active' => true,
            ]);
        }

        return $setting;
    }

    /**
     * Normalize a country name for flexible matching.
     */
    public static function normalizeCountry(?string $country): string
    {
        $clean = Str::lower(trim($country ?? ''));

        return match ($clean) {
            'us', 'usa', 'united states', 'united states of america' => 'united states',
            'uk', 'united kingdom', 'great britain', 'england' => 'united kingdom',
            'uae', 'united arab emirates' => 'united arab emirates',
            'in', 'india', 'bharat' => 'india',
            'ca', 'canada' => 'canada',
            'au', 'australia' => 'australia',
            'de', 'germany' => 'germany',
            'fr', 'france' => 'france',
            'sg', 'singapore' => 'singapore',
            'jp', 'japan' => 'japan',
            default => $clean,
        };
    }

    /**
     * Determine if a given location is deliverable based on current settings.
     */
    public function isDeliverable(?string $country, ?string $state = null, ?string $city = null): bool
    {
        if ($this->mode === 'all') {
            return true;
        }

        if (empty($country)) {
            return false;
        }

        $normCountry = self::normalizeCountry($country);
        $locations = $this->custom_locations ?? [];

        if (empty($locations)) {
            return false;
        }

        $normState = Str::lower(trim($state ?? ''));
        $normCity = Str::lower(trim($city ?? ''));

        foreach ($locations as $rule) {
            $ruleCountry = self::normalizeCountry($rule['country'] ?? '');

            if ($ruleCountry !== $normCountry) {
                continue;
            }

            // For international countries, state and city are ignored
            if ($ruleCountry !== 'india') {
                return true;
            }

            // For India, check State and City
            $ruleState = Str::lower(trim($rule['state'] ?? ''));
            $ruleCity = Str::lower(trim($rule['city'] ?? ''));

            $stateMatches = empty($ruleState) || $ruleState === 'all' || $ruleState === '*' || ($normState !== '' && $ruleState === $normState);
            $cityMatches = empty($ruleCity) || $ruleCity === 'all' || $ruleCity === '*' || ($normCity !== '' && $ruleCity === $normCity);

            if ($stateMatches && $cityMatches) {
                return true;
            }
        }

        return false;
    }
}
