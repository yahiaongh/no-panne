<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Provider;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['sometimes', 'string', 'max:150'],
            'birth_date' => ['sometimes', 'date', 'before:today'],
            'commune_id' => ['sometimes', 'integer', 'exists:communes,id'],
            'coverage_radius_km' => ['sometimes', 'integer', 'min:5', 'max:60'],
            'service_ids' => ['sometimes', 'array', 'min:1', 'max:10'],
            'service_ids.*' => ['integer', 'distinct', 'exists:services,id,active,1'],
            'coverage_wilaya_ids' => ['sometimes', 'array', 'min:1', 'max:48'],
            'coverage_wilaya_ids.*' => ['integer', 'distinct', 'exists:wilayas,id,active,1'],
            'profile_photo' => ['sometimes', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ];
    }
}