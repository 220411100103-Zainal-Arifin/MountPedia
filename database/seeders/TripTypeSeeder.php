<?php

namespace Database\Seeders;

use App\Models\TripType;
use Illuminate\Database\Seeder;

class TripTypeSeeder extends Seeder
{
    public function run(): void
    {
        TripType::updateOrCreate(['code' => 'tektok'], [
            'name' => 'Tektok',
            'description' => 'Naik dan turun dalam satu hari tanpa bermalam',
        ]);

        TripType::updateOrCreate(['code' => 'camping'], [
            'name' => 'Camping',
            'description' => 'Pendakian dengan bermalam di area camp',
        ]);
    }
}
