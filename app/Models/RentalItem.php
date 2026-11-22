<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['price_per_day_idr' => 'integer'];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(RentalShop::class, 'rental_shop_id');
    }

    public function gearItem(): BelongsTo
    {
        return $this->belongsTo(GearItem::class);
    }
}
