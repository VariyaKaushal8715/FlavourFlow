<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidStatePincode implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        $state = request()->input('state');
        if (! $state) {
            return false;
        }

        $prefix = substr($value, 0, 2);
        $allowed = config('location.pincode_state_prefixes.'.$state, []);
        if (empty($allowed)) {
            return true; // No mapping defined, assume valid.
        }

        return in_array($prefix, $allowed);
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The PIN code does not match the selected state. Please check your address.';
    }
}
