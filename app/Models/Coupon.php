<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'code',
        'type',
        'value',
        'min_booking_amount',
        'uses',
        'max_uses',
        'expires_at',
        'active',
    ];

    protected $casts = [
        'expires_at'         => 'datetime',
        'active'             => 'boolean',
        'value'              => 'decimal:2',
        'min_booking_amount' => 'decimal:2',
        'uses'               => 'integer',
        'max_uses'           => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    /** Null = platform-wide coupon */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /** Null = applies to all room types */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeValid($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')->orWhereRaw('uses < max_uses');
            });
    }

    public function scopePlatformWide($query)
    {
        return $query->whereNull('hotel_id');
    }

    // ── Validation helpers ────────────────────────────────────────────────────

    public function isValid(): bool
    {
        if (! $this->active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->uses >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function isValidForAmount(float $amount): bool
    {
        if (! $this->isValid()) {
            return false;
        }

        if ($this->min_booking_amount !== null && $amount < (float) $this->min_booking_amount) {
            return false;
        }

        return true;
    }

    /**
     * Calculate the discount amount for a given booking total.
     */
    public function calculateDiscount(float $amount): float
    {
        if (! $this->isValidForAmount($amount)) {
            return 0.0;
        }

        $discount = $this->type === 'percentage'
            ? $amount * ((float) $this->value / 100)
            : (float) $this->value;

        return round(min($discount, $amount), 2);
    }

    public function incrementUses(): void
    {
        $this->increment('uses');
    }
}
