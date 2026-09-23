<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Provider;

use Illuminate\Foundation\Http\FormRequest;

class CompleteRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount_cash' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'provider_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}