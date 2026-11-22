<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GearRecommendation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    public function tripType(): BelongsTo
    {
        return $this->belongsTo(TripType::class);
    }

    public function gearItem(): BelongsTo
    {
        return $this->belongsTo(GearItem::class);
    }

    public function mountain(): BelongsTo
    {
        return $this->belongsTo(Mountain::class);
    }

    
    public function scopeForNights(Builder $query, int $nights): Builder
    {
        return $query->where('min_nights', '<=', $nights)
            ->where(fn ($q) => $q->whereNull('max_nights')->orWhere('max_nights', '>=', $nights));
    }

    
    public function scopeForMountain(Builder $query, ?int $mountainId): Builder
    {
        return $query->where(fn ($q) => $q->whereNull('mountain_id')
            ->when($mountainId, fn ($q2) => $q2->orWhere('mountain_id', $mountainId)));
    }
}
