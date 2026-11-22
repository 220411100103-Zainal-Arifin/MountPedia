<?php

namespace Database\Factories;

use App\Models\Mountain;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Mountain> */
class MountainFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Gunung '.fake()->unique()->lastName();

        return [
            'region_id' => Region::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'elevation_mdpl' => fake()->numberBetween(1500, 3800),
            'latitude' => fake()->latitude(-9, -6),
            'longitude' => fake()->longitude(106, 116),
            'managing_authority' => fake()->randomElement([
                'Balai Taman Nasional', 'Tahura R. Soerjo', 'Perhutani', 'BKSDA', 'Pengelola basecamp',
            ]),
            'description' => fake()->paragraphs(2, true),
            'weather_area_code' => 'SAMPLE-'.strtoupper(Str::slug($name)),
            'image_url' => 'https://placehold.co/1200x630?text='.urlencode($name),
        ];
    }
}
