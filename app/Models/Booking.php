<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'user_id',
        'hotel_id',
        'coupon_id',
        'status',
        'check_in',
        'check_out',
        'nights',
        'guests_adults',
        'guests_children',
        'sub_total',
        'tax_total',
        'tax_rate',
        'discount_total',
        'coupon_code',
        'grand_total',
        'currency',
        'special_requests',
        'notes',
        'cancellation_policy_snapshot',
        'cancellation_reason',
        'cancelled_at',
        'confirmed_at',
        'checked_in_at',
        'checked_out_at',
    ];

    protected $casts = [
        'check_in'         => 'date',
        'check_out'        => 'date',
        'nights'           => 'integer',
        'guests_adults'    => 'integer',
        'guests_children'  => 'integer',
        'sub_total'        => 'decimal:2',
        'tax_total'        => 'decimal:2',
        'tax_rate'         => 'decimal:2',
        'discount_total'   => 'decimal:2',
        'grand_total'      => 'decimal:2',
        'cancelled_at'     => 'datetime',
        'confirmed_at'     => 'datetime',
        'checked_in_at'    => 'datetime',
        'checked_out_at'   => 'datetime',
    ];

    // Status constants for readability across the app
    const STATUS_PENDING     = 'pending';
    const STATUS_CONFIRMED   = 'confirmed';
    const STATUS_CHECKED_IN  = 'checked_in';
    const STATUS_CHECKED_OUT = 'checked_out';
    const STATUS_CANCELLED   = 'cancelled';
    const STATUS_REFUNDED    = 'refunded';
    const STATUS_NO_SHOW     = 'no_show';

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function rooms()
    {
        return $this->hasMany(BookingRoom::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForHotel($query, int $hotelId)
    {
        return $query->where('hotel_id', $hotelId);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_CHECKED_IN]);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED])
                     ->where('check_in', '>=', now()->toDateString());
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            self::STATUS_PENDING     => ['label' => 'Pending',      'color' => 'yellow'],
            self::STATUS_CONFIRMED   => ['label' => 'Confirmed',    'color' => 'blue'],
            self::STATUS_CHECKED_IN  => ['label' => 'Checked In',   'color' => 'green'],
            self::STATUS_CHECKED_OUT => ['label' => 'Checked Out',  'color' => 'gray'],
            self::STATUS_CANCELLED   => ['label' => 'Cancelled',    'color' => 'red'],
            self::STATUS_REFUNDED    => ['label' => 'Refunded',     'color' => 'purple'],
            self::STATUS_NO_SHOW     => ['label' => 'No Show',      'color' => 'orange'],
            default                  => ['label' => ucfirst($this->status), 'color' => 'gray'],
        };
    }

    public function getTotalGuestsAttribute(): int
    {
        return $this->guests_adults + $this->guests_children;
    }

    public function getIsCancellableAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function getIsReviewableAttribute(): bool
    {
        return $this->status === self::STATUS_CHECKED_OUT && ! $this->review()->exists();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Generate a unique, human-readable booking reference like BK-20260625-00042.
     */
    public static function generateBookingNumber(): string
    {
        $date     = now()->format('Ymd');
        $sequence = str_pad((static::whereDate('created_at', today())->count() + 1), 5, '0', STR_PAD_LEFT);

        return "BK-{$date}-{$sequence}";
    }
}
