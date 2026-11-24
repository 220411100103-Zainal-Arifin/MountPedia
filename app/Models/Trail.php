<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

    public function permitFees(): HasMany
    {
        return $this->hasMany(PermitFee::class);
    }

    /**
     * Status yang berlaku: gunung ditutup => semua jalur tutup;
     * gunung dibuka => status jalur itu sendiri.
     * Dihitung (tidak disimpan), jadi saat gunung dibuka lagi status asli tiap jalur kembali.
     * Muat relasi `mountain` lebih dulu untuk menghindari query tambahan per jalur.
     */
    protected function effectiveStatus(): Attribute
    {
        return Attribute::get(fn () => $this->mountain?->isClosed()
            ? Mountain::STATUS_CLOSED
            : $this->status);
    }

    protected function effectiveStatusReason(): Attribute
    {
        return Attribute::get(function () {
            if ($this->mountain?->isClosed()) {
                return $this->mountain->status_reason
                    ?: 'Gunung ditutup, seluruh jalur ikut ditutup.';
            }

            return $this->status_reason;
        });
    }
}
