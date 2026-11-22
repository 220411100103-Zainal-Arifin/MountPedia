<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class GearItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_rentable' => 'boolean'];
    }

    protected static function booted(): void
    {
        // slug otomatis dari nama bila belum diisi (seeder/factory/form)
        static::saving(function (self $item) {
            $item->slug ??= Str::slug($item->name);
        });
    }

    // /perlengkapan/{gearItem} akan mencari berdasarkan slug
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GearCategory::class, 'gear_category_id');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(GearRecommendation::class);
    }

    public function rentalItems(): HasMany
    {
        return $this->hasMany(RentalItem::class);
    }
}