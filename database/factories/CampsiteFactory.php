<?php

namespace Database\Factories;

use App\Models\Campsite;
use App\Models\Mountain;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Campsite> */
class CampsiteFactory extends Factory
{
    public function definition(): array
    {
        $hasWater = fake()->boolean(60);

        return [
            'mountain_id' => Mountain::factory(),
            'trail_id' => null,
            'name' => fake()->randomElement(['Pos', 'Pondok', 'Camp', 'Lembah']).' '.fake()->unique()->lastName(),
            'elevation_mdpl' => fake()->numberBetween(1200, 3300),
            'capacity_tents' => fake()->numberBetween(5, 120),
            'has_water_source' => $hasWater,
            'water_note' => $hasWater ? fake()->randomElement(['Mata air dekat lokasi', 'Aliran sungai kecil', 'Mata air ±10 menit dari camp']) : 'Tidak ada sumber air; bawa dari pos sebelumnya',
            'latitude' => fake()->latitude(-9, -6),
            'longitude' => fake()->longitude(106, 116),
            'notes' => fake()->sentence(),
        ];
    }
}
