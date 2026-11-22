<?php

namespace Database\Factories;

use App\Models\Mountain;
use App\Models\WeatherForecast;
use Illuminate\Database\Eloquent\Factories\Factory;


class WeatherForecastFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mountain_id' => Mountain::factory(),
            'point' => 'basecamp',
            'forecast_at' => now()->startOfHour()->addHours(fake()->numberBetween(0, 72)),
            'temp_c' => fake()->randomFloat(1, 12, 26),
            'humidity_pct' => fake()->numberBetween(55, 98),
            'wind_kmh' => fake()->randomFloat(1, 2, 30),
            'rain_probability_pct' => fake()->numberBetween(0, 90),
            'condition' => fake()->randomElement(['Cerah', 'Cerah Berawan', 'Berawan', 'Hujan Ringan', 'Hujan Sedang', 'Kabut']),
            'source' => 'Data contoh',
            'fetched_at' => now(),
        ];
    }

    public function summit(): static
    {
        return $this->state(fn (array $attrs) => [
            'point' => 'puncak',
            'temp_c' => max(0, $attrs['temp_c'] - 10),
            'wind_kmh' => min(99.9, $attrs['wind_kmh'] + 10),
        ]);
    }
}
