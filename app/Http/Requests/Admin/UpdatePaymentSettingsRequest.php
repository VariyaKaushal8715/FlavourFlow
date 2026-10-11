<?php

namespace App\Http\Requests\Admin;

use App\Services\RazorpayService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentSettingsRequest extends FormRequest
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
        $razorpayService = app(RazorpayService::class);
        $hasExistingSecret = $razorpayService->isConfigured() || (! empty($razorpayService->getKeySecret()) && $razorpayService->getKeySecret() !== 'rzp_test_dummy_key_secret');

        return [
            'key_id' => [
                'required',
                'string',
                'regex:/^rzp_(test|live)_[A-Za-z0-9]+$/',
            ],
            'key_secret' => [
                $hasExistingSecret ? 'nullable' : 'required',
                'string',
                'min:8',
                'regex:/^\S+$/',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'key_id.required' => 'The Razorpay Key ID is required.',
            'key_id.regex' => 'The Key ID must start with rzp_test_ or rzp_live_ followed by alphanumeric characters.',
            'key_secret.required' => 'The Razorpay Key Secret is required.',
            'key_secret.min' => 'The Razorpay Key Secret must be at least 8 characters long.',
            'key_secret.regex' => 'The Razorpay Key Secret cannot contain spaces.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('key_id')) {
            $this->merge([
                'key_id' => trim((string) $this->input('key_id')),
            ]);
        }

        if ($this->has('key_secret')) {
            $secret = trim((string) $this->input('key_secret'));
            $this->merge([
                'key_secret' => $secret !== '' ? $secret : null,
            ]);
        }
    }
}
