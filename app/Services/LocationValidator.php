<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;
use App\Rules\ValidStatePincode;

class LocationValidator
{
    /**
     * Get the list of Indian states/UTs.
     */
    public static function states(): array
    {
        return config('location.states', []);
    }

    /**
     * Validation rules for location fields.
     * Returns an empty array when the feature flag is disabled.
     */
    public static function rules(): array
    {
        if (! config('location.india_only', true)) {
            return [];
        }

        return [
            'country' => ['required', 'in:India'],
            'state'   => ['required', Rule::in(self::states())],
            'city'    => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'regex:' . config('location.pincode_regex'), new \App\Rules\ValidStatePincode()],
        ];
    }

    /**
     * Custom validation messages.
     */
    public static function messages(): array
    {
        return [
            'country.in'    => 'Currently, FlavourFlow delivery is available only within India.',
            'state.in'      => 'Please select a valid Indian state/UT.',
            'pincode.regex' => 'Please enter a valid 6-digit Indian PIN code.',
            'address.required' => 'Please check your address details and try again.',
        ];
    }

    /**
     * Run the validator and return the instance.
     */
    public static function validator(array $data)
    {
        $rules = self::rules();
        if (empty($rules)) {
            // No location validation required.
            return ValidatorFacade::make($data, []);
        }

        return ValidatorFacade::make($data, $rules, self::messages());
    }
}
