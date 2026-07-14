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
        'corporate_account_id',
        'status',
        'check_in',
        'check_out',
        'nights',
        'guests_adults',
        'guests_children',
        'sub_total',
        'addons_total',
        'tax_total',
        'tax_rate',
        'discount_total',
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
        'addons_total'     => 'decimal:2',
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

    public function corporateAccount()
    {
        return $this->belongsTo(CorporateAccount::class);
    }

    public function rooms()
    {
        return $this->hasMany(BookingRoom::class);
    }

    public function mealPackages()
    {
        return $this->hasMany(BookingMealPackage::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function messages()
    {
        return $this->hasMany(GuestMessage::class);
    }

    public function digitalCheckin()
    {
        return $this->hasOne(DigitalCheckin::class);
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

    public function cancellationApproval()
    {
        return $this->hasOne(\App\Models\CancellationApproval::class);
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

    public function scopePastGuests($query)
    {
        return $query->where('status', self::STATUS_CHECKED_OUT);
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
        return ($this->guests_adults ?? 0) + ($this->guests_children ?? 0);
    }

    /** Alias so views can use $booking->guests */
    public function getGuestsAttribute(): int
    {
        return $this->total_guests;
    }

    /** First room type on this booking — convenience for single-room bookings */
    public function getRoomTypeAttribute(): ?RoomType
    {
        return $this->rooms->first()?->roomType;
    }

    /** Payment method from the linked Payment record */
    public function getPaymentMethodAttribute(): ?string
    {
        return $this->payment?->method;
    }

    /** Payment status from the linked Payment record */
    public function getPaymentStatusAttribute(): ?string
    {
        return $this->payment?->status;
    }

    /** Alias: sub_total for views that use $booking->subtotal */
    public function getSubtotalAttribute(): float
    {
        return (float) ($this->attributes['sub_total'] ?? 0);
    }

    /** Alias: grand_total for views that use $booking->total_amount */
    public function getTotalAmountAttribute(): float
    {
        return (float) ($this->attributes['grand_total'] ?? 0);
    }

    /** Alias: tax_total for views that use $booking->tax_amount */
    public function getTaxAmountAttribute(): float
    {
        return (float) ($this->attributes['tax_total'] ?? 0);
    }

    /** Alias: discount_total for views that use $booking->discount_amount */
    public function getDiscountAmountAttribute(): float
    {
        return (float) ($this->attributes['discount_total'] ?? 0);
    }

    /** Nightly rate from the first booking room */
    public function getBasePriceAttribute(): float
    {
        return (float) ($this->rooms->first()?->nightly_rate ?? 0);
    }

    public function getIsCancellableAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /** Refund amount recorded on the linked Payment (null when no refund was issued). */
    public function getRefundAmountAttribute(): ?float
    {
        return $this->payment?->refund_amount !== null
            ? (float) $this->payment->refund_amount
            : null;
    }

    /**
     * Decode the cancellation policy snapshot stored at booking-creation time.
     * Falls back to the platform default when the booking pre-dates the structured snapshot.
     */
    public function getPolicySnapshotAttribute(): array
    {
        $raw = $this->cancellation_policy_snapshot;
        if (! $raw) {
            return app(\App\Services\CancellationService::class)->policySnapshot();
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded)
            ? $decoded
            : app(\App\Services\CancellationService::class)->policySnapshot();
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
        $date   = now()->format('Ymd');
        $prefix = "BK-{$date}-";

        $last = static::where('booking_number', 'like', $prefix . '%')
            ->orderByDesc('booking_number')
            ->value('booking_number');

        $next = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;

        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
