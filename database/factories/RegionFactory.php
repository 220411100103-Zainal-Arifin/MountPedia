<?php

namespace Database\Factories;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;


class RegionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'province' => fake()->randomElement(['Jawa Timur', 'Jawa Tengah', 'Jawa Barat', 'Nusa Tenggara Barat']),
            'regency' => 'Kabupaten '.fake()->unique()->city(),
        ];
    }
}
