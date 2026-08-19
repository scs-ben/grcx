<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'category_id',
        'racer_id',
        'finish_position',
        'laps_completed',
        'finish_time',
        'points_awarded',
    ];

    protected $casts = [
        'finish_position' => 'integer',
        'laps_completed' => 'integer',
        'points_awarded' => 'integer',
    ];

    public static function pointsForPosition(int $position): int
    {
        $scale = [
            1 => 25,
            2 => 22,
            3 => 20,
            4 => 18,
            5 => 17,
            6 => 16,
            7 => 15,
            8 => 14,
            9 => 13,
            10 => 12,
            11 => 11,
            12 => 10,
            13 => 9,
            14 => 8,
            15 => 7,
            16 => 6,
            17 => 5,
            18 => 4,
            19 => 3,
            20 => 2,
            21 => 1,
        ];

        return $scale[$position] ?? 0;
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function racer(): BelongsTo
    {
        return $this->belongsTo(Racer::class);
    }
}
