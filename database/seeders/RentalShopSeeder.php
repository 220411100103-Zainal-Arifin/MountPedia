<?php

namespace Database\Seeders;

use App\Models\GearItem;
use App\Models\Mountain;
use App\Models\Region;
use App\Models\RentalItem;
use App\Models\RentalShop;
use Illuminate\Database\Seeder;


class RentalShopSeeder extends Seeder
{
    
    private array $coverage = [
        'Kota Batu' => ['panderman', 'arjuno'],
        'Kota Malang' => ['semeru', 'arjuno', 'panderman'],
        'Kabupaten Malang' => ['semeru', 'arjuno'],
        'Kabupaten Pasuruan' => ['welirang', 'arjuno'],
        'Kabupaten Mojokerto' => ['penanggungan', 'welirang'],
        'Kabupaten Boyolali' => ['merbabu'],
        'Kabupaten Magetan' => ['lawu'],
        'Kabupaten Lombok Timur' => ['rinjani'],
    ];

    
    private array $center = [
        'Kota Batu' => [-7.8672, 112.5239],
        'Kota Malang' => [-7.9666, 112.6326],
        'Kabupaten Malang' => [-8.1844, 112.6304],
        'Kabupaten Pasuruan' => [-7.6469, 112.9075],
        'Kabupaten Mojokerto' => [-7.4704, 112.4401],
        'Kabupaten Boyolali' => [-7.5303, 110.5961],
        'Kabupaten Magetan' => [-7.6498, 111.3331],
        'Kabupaten Lombok Timur' => [-8.5389, 116.5000],
    ];

    
    private array $basePrice = [
        'Tenda dome' => 40000, 'Sleeping bag' => 15000, 'Matras' => 5000, 'Hammock' => 10000,
        'Carrier 40-60 L' => 20000, 'Daypack 20-30 L' => 10000, 'Rain cover tas' => 5000,
        'Jaket gunung' => 15000, 'Jas hujan / ponco' => 5000, 'Sarung tangan' => 5000, 'Kupluk' => 5000,
        'Sepatu gunung / trail' => 25000, 'Sandal gunung' => 10000,
        'Headlamp' => 10000, 'Powerbank' => 10000,
        'Kompor portable' => 10000, 'Nesting / panci' => 10000,
        'Botol minum 1,5 L' => 5000, 'Trekking pole' => 10000,
    ];

    public function run(): void
    {
        if (RentalShop::exists()) {
            return; 
        }

        $rentable = GearItem::where('is_rentable', true)->get();

        foreach ($this->coverage as $regency => $mountainSlugs) {
            $region = Region::where('regency', $regency)->first();
            if (! $region) {
                continue;
            }

            [$lat, $lng] = $this->center[$regency];

            RentalShop::factory()
                ->count(2)
                ->state(fn () => [
                    'region_id' => $region->id,
                    'latitude' => round($lat + fake()->randomFloat(4, -0.03, 0.03), 6),
                    'longitude' => round($lng + fake()->randomFloat(4, -0.03, 0.03), 6),
                    'is_verified' => false,
                ])
                ->create()
                ->each(function (RentalShop $shop) use ($mountainSlugs, $rentable) {
                    foreach (Mountain::whereIn('slug', $mountainSlugs)->get() as $mountain) {
                        $shop->mountains()->attach($mountain->id, [
                            'distance_km_to_basecamp' => fake()->randomFloat(1, 1, 40),
                            'note' => 'Jarak contoh',
                        ]);
                    }

                    foreach ($rentable->random(min(12, $rentable->count())) as $gear) {
                        $base = $this->basePrice[$gear->name] ?? 10000;

                        RentalItem::create([
                            'rental_shop_id' => $shop->id,
                            'gear_item_id' => $gear->id,
                            'item_name' => $gear->name,
                            
                            'price_per_day_idr' => (int) (round($base * fake()->randomFloat(2, 0.8, 1.2) / 1000) * 1000),
                            'stock_note' => fake()->randomElement(['Tersedia', 'Stok terbatas']),
                            'notes' => 'Harga belum termasuk deposit (data contoh)',
                        ]);
                    }
                });
        }
    }
}
