<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'regex:/^(?:\+213|0)[0-9]{9}$/'],
            'code' => ['required', 'string', 'digits:6'],
            'device_token' => ['nullable', 'string', 'max:500'],
            'device_platform' => ['nullable', 'string', 'in:android,ios,web'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.regex' => __('validation.phone', ['attribute' => 'phone_number']),
        ];
    }
}