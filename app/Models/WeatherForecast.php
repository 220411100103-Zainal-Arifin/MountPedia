<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherForecast extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'forecast_at' => 'datetime',
            'fetched_at' => 'datetime',
            'temp_c' => 'decimal:1',
            'wind_kmh' => 'decimal:1',
        ];
    }

    public function mountain(): BelongsTo
    {
        return $this->belongsTo(Mountain::class);
    }
}
