<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campsite extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['has_water_source' => 'boolean'];
    }

    public function mountain(): BelongsTo
    {
        return $this->belongsTo(Mountain::class);
    }

    public function trail(): BelongsTo
    {
        return $this->belongsTo(Trail::class);
    }
}
