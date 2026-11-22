<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    public function mountains(): HasMany
    {
        return $this->hasMany(Mountain::class);
    }

    public function rentalShops(): HasMany
    {
        return $this->hasMany(RentalShop::class);
    }
}
