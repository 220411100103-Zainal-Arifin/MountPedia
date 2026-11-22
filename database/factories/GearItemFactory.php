<?php

namespace Database\Factories;

use App\Models\GearCategory;
use App\Models\GearItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<GearItem> */
class GearItemFactory extends Factory
{
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->words(2, true));

        return [
            'gear_category_id' => GearCategory::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'is_rentable' => true,
            'image_url' => null, // kosong = kartu tampil dengan warna kategori + ikon
        ];
    }

    /** Perlengkapan dengan gambar, mis. GearItem::factory()->withImage()->create() */
    public function withImage(?string $path = null): static
    {
        return $this->state(fn (array $attributes) => [
            'image_url' => $path ?? '/images/gear/'.$attributes['slug'].'.jpg',
        ]);
    }

    /** Perlengkapan yang tidak disewakan */
    public function notRentable(): static
    {
        return $this->state(['is_rentable' => false]);
    }
}