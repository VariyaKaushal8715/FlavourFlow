<?php

namespace App\Support;

use Illuminate\Support\Str;

class GujaratLocation
{
    /**
     * @var array<int, string>
     */
    private const CITIES = [
        'ahmedabad',
        'amreli',
        'anand',
        'bharuch',
        'bhavnagar',
        'bhuj',
        'gandhinagar',
        'jamnagar',
        'junagadh',
        'mehsana',
        'morbi',
        'nadiad',
        'navsari',
        'palanpur',
        'porbandar',
        'rajkot',
        'surat',
        'vadodara',
        'valsad',
        'vapi',
    ];

    public static function isValidAddress(string $address): bool
    {
        return Str::of($address)->trim()->length() >= 5;
    }

    public static function isGujaratCity(string $city): bool
    {
        return in_array(Str::of($city)->trim()->lower()->toString(), self::CITIES, true);
    }

    public static function isGujaratState(string $state): bool
    {
        return Str::of($state)->trim()->lower()->toString() === 'gujarat';
    }

    /**
     * @return array<int, string>
     */
    public static function cities(): array
    {
        return array_map(fn (string $city): string => Str::of($city)->headline()->toString(), self::CITIES);
    }
}
