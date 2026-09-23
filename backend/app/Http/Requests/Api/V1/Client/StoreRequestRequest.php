<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'exists:services,id,active,1'],
            'type' => ['required', 'string', 'in:urgence,planifie'],
            'description' => ['required', 'string', 'max:500'],
            'client_address' => ['required', 'string', 'max:500'],
            'client_lat' => ['required', 'numeric', 'between:-90,90'],
            'client_lng' => ['required', 'numeric', 'between:-180,180'],
            'estimated_budget' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'scheduled_at' => ['required_if:type,planifie', 'nullable', 'date', 'after_or_equal:now'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'search_radius_km' => ['nullable', 'integer', Rule::in([10, 20, 30])],
        ];
    }
}