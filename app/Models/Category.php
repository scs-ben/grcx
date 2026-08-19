<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'wave',
        'duration_description',
        'start_order_seconds',
        'is_scoring',
        'podium_order',
    ];

    protected $casts = [
        'is_scoring' => 'boolean',
        'start_order_seconds' => 'integer',
        'podium_order' => 'integer',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }
}
