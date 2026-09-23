<?php

namespace Database\Factories;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceRequestFactory extends Factory
{
    protected $model = ServiceRequest::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'service_id' => Service::factory(),
            'type' => RequestType::Emergency,
            'description' => fake()->sentence(),
            'client_address' => fake()->streetAddress().', '.fake()->city(),
            'client_lat' => fake()->latitude(34.0, 37.0),
            'client_lng' => fake()->longitude(-1.0, 9.0),
            'status' => RequestStatus::Searching,
            'search_radius_km' => 10,
        ];
    }

    public function forProvider(\App\Models\Provider $provider): static
    {
        return $this->state(fn (array $attributes) => [
            'provider_id' => $provider->id,
            'status' => RequestStatus::OnSite,
            'accepted_at' => now()->subMinutes(10),
        ]);
    }
}