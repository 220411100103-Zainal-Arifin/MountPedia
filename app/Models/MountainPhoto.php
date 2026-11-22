<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MountainPhoto extends Model
{
    protected $guarded = [];

    public function mountain(): BelongsTo
    {
        return $this->belongsTo(Mountain::class);
    }

    
    protected function src(): Attribute
    {
        return Attribute::get(fn () => preg_match('#^(https?:)?//#', $this->path)
            ? $this->path
            : Storage::url($this->path));
    }
}
