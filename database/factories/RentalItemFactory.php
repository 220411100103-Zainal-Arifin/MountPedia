<?php

namespace Database\Factories;

use App\Models\GearItem;
use App\Models\RentalItem;
use App\Models\RentalShop;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RentalItem> */
class RentalItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rental_shop_id' => RentalShop::factory(),
            'gear_item_id' => GearItem::factory(),
            'item_name' => ucfirst(fake()->words(2, true)),
            'price_per_day_idr' => fake()->numberBetween(2, 20) * 5000,
            'stock_note' => fake()->randomElement(['Tersedia', 'Stok terbatas']),
            'notes' => 'Harga belum termasuk deposit (data contoh)',
        ];
    }
}
