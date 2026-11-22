<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripType extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    public function permitFees(): HasMany
    {
        return $this->hasMany(PermitFee::class);
    }

    public function gearRecommendations(): HasMany
    {
        return $this->hasMany(GearRecommendation::class);
    }
}
