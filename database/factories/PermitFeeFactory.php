<?php

namespace Database\Factories;

use App\Models\Mountain;
use App\Models\PermitFee;
use App\Models\TripType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PermitFee> */
class PermitFeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mountain_id' => Mountain::factory(),
            'trail_id' => null,
            'trip_type_id' => TripType::factory(),
            'visitor_type' => 'wni',
            'day_type' => fake()->randomElement(['weekday', 'weekend_libur']),
            // kelipatan Rp5.000, bilangan bulat
            'fee_idr' => fake()->numberBetween(4, 20) * 5000,
            'fee_unit' => 'per_orang',
            'valid_from' => now()->startOfYear()->toDateString(),
            'valid_until' => now()->endOfYear()->toDateString(),
            'source_url' => 'https://example.com/tarif-simaksi',
            'notes' => 'Data contoh, ganti dengan tarif resmi',
        ];
    }

    public function foreigner(): static
    {
        return $this->state(fn (array $attrs) => [
            'visitor_type' => 'wna',
            'fee_idr' => $attrs['fee_idr'] * 5,
        ]);
    }
}
