<?php

namespace Database\Seeders;

use App\Models\Campsite;
use App\Models\GearItem;
use App\Models\Mountain;
use App\Models\PermitFee;
use App\Models\Region;
use App\Models\RentalItem;
use App\Models\RentalShop;
use App\Models\Trail;
use App\Models\TrailStatusLog;
use App\Models\TripType;
use Illuminate\Database\Seeder;


class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $regionIds = Region::pluck('id');
        $tripTypes = TripType::all();
        $rentable = GearItem::where('is_rentable', true)->get();

        $mountains = Mountain::factory()
            ->count(15)
            ->state(fn () => ['region_id' => $regionIds->random()])
            ->create();

        foreach ($mountains as $mountain) {
            $trails = Trail::factory()->count(fake()->numberBetween(1, 3))->for($mountain)->create();

            Campsite::factory()
                ->count(fake()->numberBetween(1, 4))
                ->for($mountain)
                ->state(fn () => ['trail_id' => $trails->random()->id])
                ->create();

            
            $log = TrailStatusLog::factory()->for($mountain);
            match (fake()->numberBetween(1, 10)) {
                1 => $log->closed()->create(),
                2, 3 => $log->restricted()->create(),
                default => $log->create(),
            };

            foreach ($tripTypes as $trip) {
                foreach (['wni', 'wna'] as $visitor) {
                    foreach (['weekday', 'weekend_libur'] as $day) {
                        PermitFee::factory()->for($mountain)->state([
                            'trip_type_id' => $trip->id,
                            'visitor_type' => $visitor,
                            'day_type' => $day,
                            'fee_unit' => $trip->code === 'camping' ? 'per_orang_per_malam' : 'per_orang',
                        ])->create();
                    }
                }
            }
        }

        $shops = RentalShop::factory()
            ->count(10)
            ->state(fn () => ['region_id' => $regionIds->random()])
            ->create();

        foreach ($shops as $shop) {
            $shop->mountains()->attach(
                $mountains->random(fake()->numberBetween(1, 3))->pluck('id')
                    ->mapWithKeys(fn ($id) => [$id => ['distance_km_to_basecamp' => fake()->randomFloat(1, 1, 40)]])
                    ->all()
            );

            foreach ($rentable->random(min(8, $rentable->count())) as $gear) {
                RentalItem::factory()->for($shop, 'shop')->state([
                    'gear_item_id' => $gear->id,
                    'item_name' => $gear->name,
                ])->create();
            }
        }

        $this->call(WeatherSeeder::class);
    }
}
