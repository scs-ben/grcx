<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'event_date',
        'season_year',
        'description',
        'is_active',
    ];

    protected $casts = [
        'event_date' => 'date:Y-m-d',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'formatted_date',
    ];

    public function getFormattedDateAttribute(): string
    {
        return $this->event_date ? $this->event_date->format('M j, Y') : '';
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }
}
