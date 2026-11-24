<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mountain extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'buka';

    public const STATUS_CLOSED = 'tutup';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'elevation_mdpl' => 'integer',
            'latitude' => 'decimal:6',
            'longitude' => 'decimal:6',
            'status_updated_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
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

    public function gearRecommendations(): HasMany
    {
        return $this->hasMany(GearRecommendation::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(MountainPhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    public function rentalShops(): BelongsToMany
    {
        return $this->belongsToMany(RentalShop::class, 'shop_mountains')
            ->withPivot(['distance_km_to_basecamp', 'note']);
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /**
     * Alias lama: komponen Blade yang masih membaca `current_status`
     * (mis. x-mountain.card) tetap jalan. Pakai `status` untuk kode baru.
     */
    protected function currentStatus(): Attribute
    {
        return Attribute::get(fn () => $this->status);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    public function scopeElevationBetween(Builder $query, ?int $min, ?int $max): Builder
    {
        return $query
            ->when($min, fn ($q) => $q->where('elevation_mdpl', '>=', $min))
            ->when($max, fn ($q) => $q->where('elevation_mdpl', '<=', $max));
    }
}
