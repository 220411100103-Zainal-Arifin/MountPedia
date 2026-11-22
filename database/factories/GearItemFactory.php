<?php

namespace Database\Factories;

use App\Models\GearCategory;
use App\Models\GearItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


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
            'image_url' => null, 
        ];
    }

    
    public function withImage(?string $path = null): static
    {
        return $this->state(fn (array $attributes) => [
            'image_url' => $path ?? '/images/gear/'.$attributes['slug'].'.jpg',
        ]);
    }

    
    public function notRentable(): static
    {
        return $this->state(['is_rentable' => false]);
    }
}