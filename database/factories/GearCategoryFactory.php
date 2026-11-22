<?php

namespace Database\Factories;

use App\Models\GearCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GearCategory> */
class GearCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->words(2, true)),
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
