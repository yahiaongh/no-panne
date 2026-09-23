<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Models\Client;
use App\Models\User;
use App\Models\Wilaya;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create()->id,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'wilaya_id' => Wilaya::query()->inRandomOrder()->first()?->id,
            'language' => 'fr',
            'status' => AccountStatus::Active,
        ];
    }
}