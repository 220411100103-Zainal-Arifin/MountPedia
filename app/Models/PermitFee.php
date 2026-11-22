<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermitFee extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'fee_idr' => 'integer',
            'valid_from' => 'date',
            'valid_until' => 'date',
        ];
    }

    public function mountain(): BelongsTo
    {
        return $this->belongsTo(Mountain::class);
    }

    public function trail(): BelongsTo
    {
        return $this->belongsTo(Trail::class);
    }

    public function tripType(): BelongsTo
    {
        return $this->belongsTo(TripType::class);
    }
}
