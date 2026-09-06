<?php
namespace App\Services;

use Illuminate\Support\Facades\Log;

class AddressValidationService
{
    // Validation result constants
    public const VALID = 'VALID';
    public const INVALID_FORMAT = 'INVALID_FORMAT';
    public const INVALID_PIN = 'INVALID_PIN';
    public const LOCATION_MISMATCH = 'LOCATION_MISMATCH';
    public const ADDRESS_NOT_FOUND = 'ADDRESS_NOT_FOUND';
    public const VERIFICATION_UNAVAILABLE = 'VERIFICATION_UNAVAILABLE';

    /**
     * Validate an address payload.
     * Expected keys: address, city, state, country, pincode
     * Returns one of the result constants.
     */
    public static function validate(array $data): string
    {
        $required = ['address', 'city', 'state', 'country', 'pincode'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return self::INVALID_FORMAT;
            }
        }

        // India‑only restriction if enabled
        if (config('address.india_only', true) && strtolower($data['country']) !== 'india') {
            return self::LOCATION_MISMATCH;
        }

        // PIN code format validation
        if (!preg_match(config('address.pincode_regex'), $data['pincode'])) {
            return self::INVALID_PIN;
        }

        // Attempt static dataset lookup
        $datasetPath = config('address.pincode_dataset');
        if (is_file($datasetPath)) {
            $json = file_get_contents($datasetPath);
            $map = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($map[$data['pincode']])) {
                $info = $map[$data['pincode']];
                if (isset($info['state']) && strcasecmp($info['state'], $data['state']) !== 0) {
                    return self::LOCATION_MISMATCH;
                }
                return self::VALID;
            }
        }

        // Fallback to external geocode provider if configured
        $providerClass = config('address.geocode_provider');
        if ($providerClass) {
            try {
                $provider = app($providerClass);
                $verified = $provider->verify([
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'country' => $data['country'],
                    'pincode' => $data['pincode'],
                ]);
                return $verified ? self::VALID : self::ADDRESS_NOT_FOUND;
            } catch (\Exception $e) {
                Log::error('Address verification provider error: ' . $e->getMessage());
                return self::VERIFICATION_UNAVAILABLE;
            }
        }

        // All format and PIN regex rules passed successfully
        return self::VALID;
    }

    public static function message(string $result): string
    {
        return match ($result) {
            self::VALID => 'Address validated successfully.',
            self::INVALID_FORMAT => 'Please fill in all address fields.',
            self::INVALID_PIN => 'Please provide a valid 6‑digit Indian PIN code.',
            self::LOCATION_MISMATCH => 'Delivery is currently limited to India.',
            self::ADDRESS_NOT_FOUND => 'We could not verify the address. Please check the details.',
            self::VERIFICATION_UNAVAILABLE => 'Address verification service is currently unavailable.',
            default => 'Invalid address data.',
        };
    }
}
?>

