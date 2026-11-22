<?php

namespace Database\Seeders;

use App\Models\Mountain;
use App\Models\PermitFee;
use App\Models\TripType;
use Illuminate\Database\Seeder;

/**
 * Tarif di bawah ini adalah PLACEHOLDER untuk pengembangan, bukan tarif resmi.
 * Ganti dengan tarif dari pengelola kawasan dan isi source_url.
 */
class PermitFeeSeeder extends Seeder
{
    public function run(): void
    {
        $tektok = TripType::where('code', 'tektok')->firstOrFail();
        $camping = TripType::where('code', 'camping')->firstOrFail();

        // [trip type, satuan, weekday WNI, weekend/libur WNI]
        $plans = [
            [$tektok, 'per_orang', 20000, 25000],
            [$camping, 'per_orang_per_malam', 25000, 30000],
        ];
        $wnaMultiplier = 5;

        foreach (Mountain::all() as $mountain) {
            foreach ($plans as [$trip, $unit, $weekday, $weekend]) {
                foreach (['wni' => 1, 'wna' => $wnaMultiplier] as $visitor => $mult) {
                    foreach (['weekday' => $weekday, 'weekend_libur' => $weekend] as $dayType => $base) {
                        PermitFee::updateOrCreate(
                            [
                                'mountain_id' => $mountain->id,
                                'trail_id' => null,
                                'trip_type_id' => $trip->id,
                                'visitor_type' => $visitor,
                                'day_type' => $dayType,
                            ],
                            [
                                'fee_idr' => $base * $mult,
                                'fee_unit' => $unit,
                                'valid_from' => now()->startOfYear()->toDateString(),
                                'valid_until' => now()->endOfYear()->toDateString(),
                                'source_url' => 'https://example.com/tarif-simaksi',
                                'notes' => 'Placeholder, ganti dengan tarif resmi',
                            ]
                        );
                    }
                }
            }
        }
    }
}
