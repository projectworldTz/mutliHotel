<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'order_id',     // legacy – nullable, kept until Phase 11 cleanup
        'method',
        'status',
        'transaction_id',
        'amount',
        'refund_amount',
        'currency',
        'metadata',
    ];

    protected $casts = [
        'metadata'      => 'array',
        'amount'        => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    // status values: pending|paid|failed|refunded|cancelled

    // ── Relationships ─────────────────────────────────────────────────────────

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'paid'      => ['label' => 'Paid',      'color' => 'green'],
            'pending'   => ['label' => 'Pending',   'color' => 'yellow'],
            'failed'    => ['label' => 'Failed',    'color' => 'red'],
            'refunded'  => ['label' => 'Refunded',  'color' => 'purple'],
            'cancelled' => ['label' => 'Cancelled', 'color' => 'gray'],
            default     => ['label' => ucfirst($this->status), 'color' => 'gray'],
        };
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            'airtel_money' => 'Airtel Money',
            'mpesa'        => 'M-Pesa',
            'halotel'      => 'Halotel',
            'mix_by_yas'   => 'Mix by Yas',
            'dpo_card'     => 'Card Payment (DPO Pay)',
            default        => ucfirst(str_replace('_', ' ', $this->method)),
        };
    }
}
