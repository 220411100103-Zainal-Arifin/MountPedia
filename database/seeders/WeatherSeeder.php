<?php

namespace Database\Seeders;

use App\Models\Mountain;
use App\Models\WeatherForecast;
use Illuminate\Database\Seeder;

/**
 * Prakiraan cuaca acak untuk pengembangan UI.
 * Pada produksi tabel ini diisi oleh scheduled command yang mengambil data dari penyedia cuaca.
 */
class WeatherSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Mountain::all() as $mountain) {
            foreach (['basecamp', 'puncak'] as $point) {
                foreach (range(0, 2) as $dayOffset) {
                    foreach ([6, 12, 18] as $hour) {
                        $at = now()->startOfDay()->addDays($dayOffset)->addHours($hour);

                        $factory = WeatherForecast::factory();
                        if ($point === 'puncak') {
                            $factory = $factory->summit();
                        }

                        $attrs = $factory->raw([
                            'mountain_id' => $mountain->id,
                            'point' => $point,
                            'forecast_at' => $at,
                        ]);

                        WeatherForecast::updateOrCreate(
                            ['mountain_id' => $mountain->id, 'point' => $point, 'forecast_at' => $at],
                            $attrs
                        );
                    }
                }
            }
        }
    }
}
