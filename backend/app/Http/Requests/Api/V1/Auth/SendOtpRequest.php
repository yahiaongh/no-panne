<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'regex:/^(?:\+213|0)[0-9]{9}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.regex' => __('validation.phone', ['attribute' => 'phone_number']),
        ];
    }
}