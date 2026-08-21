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

    public static function timeToSeconds(?string $time): ?int
    {
        if (! $time) {
            return null;
        }

        $time = trim($time);
        if ($time === '' || $time === '—' || $time === '-') {
            return null;
        }

        $parts = explode(':', $time);
        if (count($parts) === 3) {
            return ((int) $parts[0] * 3600) + ((int) $parts[1] * 60) + (int) $parts[2];
        }

        if (count($parts) === 2) {
            return ((int) $parts[0] * 60) + (int) $parts[1];
        }

        if (count($parts) === 1 && is_numeric($parts[0])) {
            return (int) $parts[0];
        }

        return null;
    }

    public static function secondsToTime(?int $seconds): ?string
    {
        if ($seconds === null) {
            return null;
        }

        $hours = intdiv($seconds, 3600);
        $remainder = $seconds % 3600;
        $minutes = intdiv($remainder, 60);
        $secs = $remainder % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%02d:%02d', $minutes, $secs);
    }

    /**
     * @return array<int, array{
     *     racer: Racer,
     *     wave_c_time: string|null,
     *     wave_c_laps: int,
     *     wave_c_position: int|null,
     *     wave_c_category: string|null,
     *     wave_b_time: string|null,
     *     wave_b_laps: int,
     *     wave_b_position: int|null,
     *     wave_b_category: string|null,
     *     total_laps: int,
     *     total_seconds: int|null,
     *     combined_time: string|null,
     *     is_complete: bool,
     *     finish_position: int
     * }>
     */
    public static function calculateBcCombinedForEvent(int $eventId): array
    {
        $results = self::where('event_id', $eventId)
            ->with(['racer.team', 'category'])
            ->get();

        $groupedByRacer = $results->groupBy('racer_id');
        $bcList = [];

        foreach ($groupedByRacer as $racerId => $racerResults) {
            /** @var self|null $cRes */
            $cRes = $racerResults->first(fn (self $r) => $r->category?->wave === 'C');
            /** @var self|null $bRes */
            $bRes = $racerResults->first(fn (self $r) => $r->category?->wave === 'B');

            $isExplicitBc = $racerResults->contains(fn (self $r) => $r->category && str_contains($r->category->name, 'BC'));

            // Include racer if they have results in both B and C waves, or explicit BC category
            if (! ($isExplicitBc || ($cRes && $bRes))) {
                continue;
            }

            $racer = $racerResults->first()->racer;
            if (! $racer) {
                continue;
            }

            $cLaps = $cRes ? $cRes->laps_completed : 0;
            $bLaps = $bRes ? $bRes->laps_completed : 0;
            $totalLaps = $cLaps + $bLaps;

            $cSeconds = self::timeToSeconds($cRes?->finish_time);
            $bSeconds = self::timeToSeconds($bRes?->finish_time);

            $totalSeconds = null;
            if ($cSeconds !== null || $bSeconds !== null) {
                $totalSeconds = ($cSeconds ?? 0) + ($bSeconds ?? 0);
            }

            $isComplete = ($cRes !== null && $bRes !== null);

            $bcList[] = [
                'racer' => $racer,
                'wave_c_time' => $cRes?->finish_time,
                'wave_c_laps' => $cLaps,
                'wave_c_position' => $cRes?->finish_position,
                'wave_c_category' => $cRes?->category?->name,
                'wave_b_time' => $bRes?->finish_time,
                'wave_b_laps' => $bLaps,
                'wave_b_position' => $bRes?->finish_position,
                'wave_b_category' => $bRes?->category?->name,
                'total_laps' => $totalLaps,
                'total_seconds' => $totalSeconds,
                'combined_time' => self::secondsToTime($totalSeconds),
                'is_complete' => $isComplete,
                'finish_position' => 0,
            ];
        }

        // Sort: Complete first, then most laps completed (descending), then lowest total seconds (ascending)
        usort($bcList, function ($a, $b) {
            if ($a['is_complete'] !== $b['is_complete']) {
                return $b['is_complete'] <=> $a['is_complete'];
            }

            if ($a['total_laps'] !== $b['total_laps']) {
                return $b['total_laps'] <=> $a['total_laps'];
            }

            if ($a['total_seconds'] !== null && $b['total_seconds'] !== null) {
                return $a['total_seconds'] <=> $b['total_seconds'];
            }

            if ($a['total_seconds'] !== null) {
                return -1;
            }

            if ($b['total_seconds'] !== null) {
                return 1;
            }

            return 0;
        });

        // Assign finish positions
        foreach ($bcList as $index => &$entry) {
            $entry['finish_position'] = $index + 1;
        }

        return $bcList;
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Racer, $this>
     */
    public function racer(): BelongsTo
    {
        return $this->belongsTo(Racer::class);
    }
}
