<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserProfileRequest extends FormRequest
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
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'country' => ['required', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'pincode' => ['nullable', 'string', 'max:20'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('pincode') && ! $this->has('postal_code')) {
            $this->merge(['postal_code' => $this->input('pincode')]);
        } elseif ($this->has('postal_code') && ! $this->has('pincode')) {
            $this->merge(['pincode' => $this->input('postal_code')]);
        }
    }

    public function messages(): array
    {
        return [];
    }
}
