<?php

namespace Database\Factories;

use App\Models\Mountain;
use App\Models\TrailStatusLog;
use Illuminate\Database\Eloquent\Factories\Factory;


class TrailStatusLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mountain_id' => Mountain::factory(),
            'trail_id' => null,
            'status' => 'buka',
            'reason' => 'Jalur dibuka normal (data contoh)',
            'starts_on' => now()->subDays(fake()->numberBetween(1, 30))->toDateString(),
            'ends_on' => null,
            'source_name' => 'Data contoh',
            'source_url' => 'https://example.com/pengumuman-pengelola',
            'reported_at' => now()->subHours(fake()->numberBetween(1, 72)),
        ];
    }

    public function closed(string $reason = 'Penutupan sementara (data contoh)'): static
    {
        return $this->state(fn () => [
            'status' => 'tutup',
            'reason' => $reason,
            'ends_on' => now()->addDays(fake()->numberBetween(3, 30))->toDateString(),
        ]);
    }

    public function restricted(string $reason = 'Pembatasan kuota (data contoh)'): static
    {
        return $this->state(fn () => ['status' => 'dibatasi', 'reason' => $reason]);
    }
}
