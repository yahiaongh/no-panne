<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Provider;

use App\Enums\VehicleType;
use Illuminate\Foundation\Http\FormRequest;

class RegisterProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'birth_date' => ['required', 'date', 'before:today', 'after:'.now()->subYears(100)->toDateString()],
            'wilaya_id' => ['required', 'integer', 'exists:wilayas,id,active,1'],
            'commune_id' => ['required', 'integer', 'exists:communes,id'],
            'coverage_radius_km' => ['required', 'integer', 'min:5', 'max:60'],
            'service_ids' => ['required', 'array', 'min:1', 'max:10'],
            'service_ids.*' => ['integer', 'distinct', 'exists:services,id,active,1'],
            'coverage_wilaya_ids' => ['nullable', 'array', 'min:1', 'max:48'],
            'coverage_wilaya_ids.*' => ['integer', 'distinct', 'exists:wilayas,id,active,1'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'vehicle' => ['required', 'array'],
            'vehicle.vehicle_type' => ['required', 'string', 'in:'.implode(',', VehicleType::values())],
            'vehicle.brand' => ['required', 'string', 'max:100'],
            'vehicle.model' => ['required', 'string', 'max:100'],
            'vehicle.plate' => ['required', 'string', 'max:20', 'unique:vehicles,plate'],
            'vehicle.year' => ['nullable', 'integer', 'between:1980,2100'],
            'current_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'current_lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}