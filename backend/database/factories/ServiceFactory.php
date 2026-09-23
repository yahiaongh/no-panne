<?php

namespace Database\Factories;

use App\Enums\ServiceType;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'code' => 'SRV-'.fake()->unique()->numerify('##'),
            'name_fr' => fake()->sentence(3),
            'type' => ServiceType::Planned,
            'active' => true,
            'sort_order' => 0,
        ];
    }

    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ServiceType::Emergency,
        ]);
    }
}