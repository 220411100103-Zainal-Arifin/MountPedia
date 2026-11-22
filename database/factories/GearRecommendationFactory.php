<?php

namespace Database\Factories;

use App\Models\GearItem;
use App\Models\GearRecommendation;
use App\Models\TripType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GearRecommendation> */
class GearRecommendationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'trip_type_id' => TripType::factory(),
            'gear_item_id' => GearItem::factory(),
            'mountain_id' => null,
            'min_nights' => 0,
            'max_nights' => null,
            'priority' => fake()->randomElement(['wajib', 'disarankan', 'opsional']),
            'quantity_note' => fake()->randomElement(['1 per orang', '1 per kelompok', '2 buah']),
            'notes' => fake()->sentence(),
        ];
    }
}
