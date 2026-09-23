<?php

namespace Database\Factories;

use App\Models\Wilaya;
use Illuminate\Database\Eloquent\Factories\Factory;

class WilayaFactory extends Factory
{
    protected $model = Wilaya::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('##'),
            'name_fr' => fake()->city(),
            'active' => true,
        ];
    }
}