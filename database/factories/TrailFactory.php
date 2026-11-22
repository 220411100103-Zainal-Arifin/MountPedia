<?php

namespace Database\Factories;

use App\Models\Mountain;
use App\Models\Trail;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Trail> */
class TrailFactory extends Factory
{
    public function definition(): array
    {
        $village = fake()->unique()->lastName();
        $up = fake()->randomFloat(1, 2, 14);

        return [
            'mountain_id' => Mountain::factory(),
            'name' => 'Jalur via '.$village,
            'slug' => Str::slug('via '.$village),
            'basecamp_name' => 'Basecamp '.$village,
            'basecamp_latitude' => fake()->latitude(-9, -6),
            'basecamp_longitude' => fake()->longitude(106, 116),
            'start_elevation_mdpl' => fake()->numberBetween(600, 2400),
            'distance_km' => fake()->randomFloat(2, 3, 20),
            'duration_up_hours' => $up,
            'duration_down_hours' => round($up * 0.65, 1),
            'difficulty' => fake()->randomElement(['mudah', 'sedang', 'sulit', 'ekstrem']),
            'description' => fake()->paragraph(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
