<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trail extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'duration_up_hours' => 'decimal:1',
            'duration_down_hours' => 'decimal:1',
            'is_active' => 'boolean',
        ];
    }

    public function mountain(): BelongsTo
    {
        return $this->belongsTo(Mountain::class);
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
}
