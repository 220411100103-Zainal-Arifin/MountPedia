<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mountain extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'elevation_mdpl' => 'integer',
            'latitude' => 'decimal:6',
            'longitude' => 'decimal:6',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug'; // URL berbasis slug (N-06, N-07)
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function trails(): HasMany
    {
        return $this->hasMany(Trail::class);
    }

    public function campsites(): HasMany
    {
        return $this->hasMany(Campsite::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(TrailStatusLog::class);
    }

    public function permitFees(): HasMany
    {
        return $this->hasMany(PermitFee::class);
    }

    public function weatherForecasts(): HasMany
    {
        return $this->hasMany(WeatherForecast::class);
    }

    public function gearRecommendations(): HasMany
    {
        return $this->hasMany(GearRecommendation::class);
    }

    public function rentalShops(): BelongsToMany
    {
        return $this->belongsToMany(RentalShop::class, 'shop_mountains')
            ->withPivot(['distance_km_to_basecamp', 'note']);
    }

    /** Filter ketinggian (US-02). */
    public function scopeElevationBetween(Builder $query, ?int $min, ?int $max): Builder
    {
        return $query
            ->when($min, fn ($q) => $q->where('elevation_mdpl', '>=', $min))
            ->when($max, fn ($q) => $q->where('elevation_mdpl', '<=', $max));
    }

    public function photos(): HasMany
    {
        return $this->hasMany(MountainPhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeWithCurrentStatus(Builder $query): Builder
    {
        return $query->addSelect(['current_status' => TrailStatusLog::query()
            ->select('status')
            ->whereColumn('trail_status_logs.mountain_id', 'mountains.id')
            ->mountainLevel()
            ->activeToday()
            ->orderByDesc('reported_at')
            ->limit(1)]);
    }
}
