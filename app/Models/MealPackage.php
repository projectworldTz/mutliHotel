<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealPackage extends Model
{
    const PRICING_PER_NIGHT = 'per_night';
    const PRICING_PER_STAY  = 'per_stay';
    const PRICING_PER_GUEST = 'per_guest';

    protected $fillable = [
        'hotel_id', 'name', 'description', 'price', 'pricing_type', 'active',
    ];

    protected $casts = [
        'price'  => 'decimal:2',
        'active' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function hotel(): BelongsTo { return $this->belongsTo(Hotel::class); }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForHotel($query, int $hotelId) { return $query->where('hotel_id', $hotelId); }
    public function scopeActive($query)                 { return $query->where('active', true); }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * How many units of this package a booking should be charged for,
     * based on its pricing type — kept in one place so BookingService
     * doesn't duplicate the switch logic.
     */
    public function calculateQuantity(int $nights, int $guests): int
    {
        return match ($this->pricing_type) {
            self::PRICING_PER_NIGHT => max($nights, 1),
            self::PRICING_PER_GUEST => max($guests, 1),
            default                 => 1,
        };
    }
}
