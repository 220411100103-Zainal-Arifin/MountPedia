<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['Jawa Timur', 'Kota Batu'],
            ['Jawa Timur', 'Kota Malang'],
            ['Jawa Timur', 'Kabupaten Malang'],
            ['Jawa Timur', 'Kabupaten Lumajang'],
            ['Jawa Timur', 'Kabupaten Pasuruan'],
            ['Jawa Timur', 'Kabupaten Mojokerto'],
            ['Jawa Timur', 'Kabupaten Magetan'],
            ['Jawa Tengah', 'Kabupaten Boyolali'],
            ['Jawa Tengah', 'Kabupaten Karanganyar'],
            ['Nusa Tenggara Barat', 'Kabupaten Lombok Timur'],
            ['Nusa Tenggara Barat', 'Kabupaten Lombok Utara'],
        ];

        foreach ($regions as [$province, $regency]) {
            Region::firstOrCreate(['province' => $province, 'regency' => $regency]);
        }
    }
}
