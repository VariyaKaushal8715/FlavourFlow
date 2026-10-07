<?php

namespace App\Http\Requests\Account;

use App\Support\GujaratLocation;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:120'],
            'mobile_number' => ['required', 'string', 'max:25'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'address' => [
                'required', 'string', 'max:1000',
                function ($attribute, $value, $fail) {
                    if (! GujaratLocation::isValidAddress($value)) {
                        $fail('Only Gujarat addresses are supported.');
                    }
                },
            ],
            'city' => [
                'required', 'string', 'max:120',
                function ($attribute, $value, $fail) {
                    if (! GujaratLocation::isGujaratCity($value)) {
                        $fail('Only Gujarat cities are supported.');
                    }
                },
            ],
            'state' => [
                'required', 'string', 'max:120',
                function ($attribute, $value, $fail) {
                    if (! GujaratLocation::isGujaratState($value)) {
                        $fail('Only Gujarat state is supported.');
                    }
                },
            ],
            'country' => ['required', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:20'],
        ];
    }
}
