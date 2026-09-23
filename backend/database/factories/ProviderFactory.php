<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Models\Commune;
use App\Models\Provider;
use App\Models\User;
use App\Models\Wilaya;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    public function definition(): array
    {
        $wilaya = Wilaya::query()->inRandomOrder()->first() ?? Wilaya::factory()->create();
        $commune = Commune::query()->where('wilaya_id', $wilaya->id)->inRandomOrder()->first()
            ?? Commune::factory()->create(['wilaya_id' => $wilaya->id]);

        return [
            'user_id' => User::factory()->provider()->create()->id,
            'full_name' => fake()->name(),
            'birth_date' => fake()->date('Y-m-d', '-25 years'),
            'wilaya_id' => $wilaya->id,
            'commune_id' => $commune->id,
            'coverage_radius_km' => fake()->numberBetween(5, 30),
            'is_available' => true,
            'current_lat' => fake()->latitude(34.0, 37.0),
            'current_lng' => fake()->longitude(-1.0, 9.0),
            'status' => AccountStatus::Active,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AccountStatus::Pending,
            'is_available' => false,
        ]);
    }
}