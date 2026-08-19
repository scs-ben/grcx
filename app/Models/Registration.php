<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'racer_id',
        'event_id',
        'category_id',
        'season_year',
        'fee_type',
        'payment_method',
        'amount_paid',
        'is_season_pass',
        'clothespin_number',
        'is_checked_in',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'is_season_pass' => 'boolean',
        'is_checked_in' => 'boolean',
        'season_year' => 'integer',
    ];

    public function racer(): BelongsTo
    {
        return $this->belongsTo(Racer::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
