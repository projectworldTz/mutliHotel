<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'type',
        'rate',
        'status',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'rate'           => 'decimal:2',
        'effective_from' => 'date',
        'effective_to'   => 'date',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeEffectiveOn($query, string $date)
    {
        return $query->where('effective_from', '<=', $date)
                     ->where(function ($q) use ($date) {
                         $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date);
                     });
    }

    /**
     * Get the platform default commission rate for a given date.
     * hotel_id IS NULL means it is the platform-wide default.
     */
    public static function platformRateOn(string $date): float
    {
        $commission = static::whereNull('hotel_id')
            ->active()
            ->effectiveOn($date)
            ->orderByDesc('effective_from')
            ->first();

        return $commission ? (float) $commission->rate : 10.0;
    }

    /**
     * Resolve the effective rate for a hotel on a given date.
     * Falls back to platform default if no hotel-specific rule exists.
     */
    public static function rateForHotel(Hotel $hotel, string $date): float
    {
        $commission = static::where('hotel_id', $hotel->id)
            ->active()
            ->effectiveOn($date)
            ->orderByDesc('effective_from')
            ->first();

        return $commission
            ? (float) $commission->rate
            : static::platformRateOn($date);
    }

    /**
     * Calculate the commission amount for a given booking total.
     */
    public function calculate(float $amount): float
    {
        return $this->type === 'percentage'
            ? round($amount * $this->rate / 100, 2)
            : (float) $this->rate;
    }
}
