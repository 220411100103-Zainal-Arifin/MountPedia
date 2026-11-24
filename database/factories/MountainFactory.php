<?php

namespace Database\Factories;

use App\Models\Mountain;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'image_url' => 'https://placehold.co/1200x630?text='.urlencode($name),
            'status' => Mountain::STATUS_OPEN,
            'status_reason' => null,
            'status_source_url' => null,
            'status_updated_at' => now(),
        ];
    }

    public function closed(string $reason = 'Penutupan sementara (data contoh)'): static
    {
        return $this->state(fn () => [
            'status' => Mountain::STATUS_CLOSED,
            'status_reason' => $reason,
            'status_source_url' => 'https://example.com/pengumuman-pengelola',
            'status_updated_at' => now(),
        ]);
    }
}
