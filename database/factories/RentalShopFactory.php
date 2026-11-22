<?php

namespace Database\Factories;

use App\Models\Region;
use App\Models\RentalShop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<RentalShop> */
class RentalShopFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Rimba', 'Puncak', 'Carrier', 'Summit', 'Basecamp', 'Kabut'])
            .' Outdoor '.fake()->unique()->lastName();

        return [
            'region_id' => Region::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'address' => fake()->streetAddress(),
            'latitude' => fake()->latitude(-9, -6),
            'longitude' => fake()->longitude(106, 116),
            'phone' => fake()->numerify('0812-0000-####'),
            'whatsapp' => fake()->numerify('0812-0000-####'),
            'opening_hours' => fake()->randomElement(['08.00–21.00', '07.00–22.00', '24 jam (reservasi)']),
            'description' => fake()->sentence(),
            'is_verified' => false,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => ['is_verified' => true]);
    }
}
