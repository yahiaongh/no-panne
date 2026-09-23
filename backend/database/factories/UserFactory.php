<?php

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    public function definition(): array
    {
        return [
            'phone_number' => '+213'.fake()->numerify('6########'),
            'country_code' => '+213',
            'role_type' => UserRole::Client,
            'email' => fake()->unique()->safeEmail(),
            'phone_verified_at' => now(),
            'password' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone_verified_at' => null,
        ]);
    }

    public function provider(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_type' => UserRole::Provider,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_type' => UserRole::Admin,
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
        ]);
    }
}