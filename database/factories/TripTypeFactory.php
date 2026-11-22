<?php

namespace Database\Factories;

use App\Models\TripType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TripType> */
class TripTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->slug(1),
            'name' => fake()->word(),
            'description' => fake()->sentence(),
        ];
    }

    public function tektok(): static
    {
        return $this->state(fn () => [
            'code' => 'tektok',
            'name' => 'Tektok',
            'description' => 'Naik dan turun dalam satu hari tanpa bermalam',
        ]);
    }

    public function camping(): static
    {
        return $this->state(fn () => [
            'code' => 'camping',
            'name' => 'Camping',
            'description' => 'Pendakian dengan bermalam di area camp',
        ]);
    }
}
