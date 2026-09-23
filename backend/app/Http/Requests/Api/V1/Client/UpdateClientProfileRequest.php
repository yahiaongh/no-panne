<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'wilaya_id' => ['nullable', 'integer', 'exists:wilayas,id'],
            'language' => ['nullable', 'string', 'in:fr,ar'],
        ];
    }
}