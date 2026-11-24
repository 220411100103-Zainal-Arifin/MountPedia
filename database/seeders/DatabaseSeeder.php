<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RegionSeeder::class,
            TripTypeSeeder::class,
            MountainSeeder::class,
            GearSeeder::class,
            TrailStatusSeeder::class,
            PermitFeeSeeder::class,
            RentalShopSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
