<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalShop extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_verified' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function mountains(): BelongsToMany
    {
        return $this->belongsToMany(Mountain::class, 'shop_mountains')
            ->withPivot(['distance_km_to_basecamp', 'note']);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RentalItem::class);
    }
}
