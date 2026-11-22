<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Urutan mengikuti ketergantungan foreign key.
     *
     * PERHATIAN: seluruh data awal bersifat CONTOH / PERKIRAAN untuk pengembangan.
     * Verifikasi ke pengelola kawasan sebelum dipublikasikan (lihat N-03: sumber & tanggal pembaruan).
     *
     * Data tambahan untuk uji pagination/pencarian:
     *   php artisan db:seed --class=DemoDataSeeder
     */
    public function run(): void
    {
        $this->call([
            RegionSeeder::class,
            TripTypeSeeder::class,
            MountainSeeder::class,      // gunung + jalur + camp
            GearSeeder::class,          // kategori, item, aturan rekomendasi
            TrailStatusSeeder::class,
            PermitFeeSeeder::class,
            WeatherSeeder::class,
            RentalShopSeeder::class,
        ]);
    }
}
