<?php

namespace App\Http\Requests\Account;

use Closure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserProfileMobileNumberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'mobile_number' => [
                'required',
                'string',
                'max:25',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || preg_match('/^[0-9+\-\s()]{7,25}$/', $value) !== 1) {
                        $fail('The mobile number must be a valid phone number.');
                    }
                },
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Please check the mobile number and try again.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
