<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'invoice_number',
        'subtotal', 'addons_total', 'tax_total', 'discount_total', 'grand_total',
        'cancellation_deduction', 'refund_amount', 'deduction_percentage',
        'currency', 'status',
        'issued_at', 'due_at', 'paid_at', 'cancelled_at', 'refunded_at',
        'notes',
    ];

    protected $casts = [
        'subtotal'               => 'decimal:2',
        'addons_total'           => 'decimal:2',
        'tax_total'              => 'decimal:2',
        'discount_total'         => 'decimal:2',
        'grand_total'            => 'decimal:2',
        'cancellation_deduction' => 'decimal:2',
        'refund_amount'          => 'decimal:2',
        'deduction_percentage'   => 'decimal:2',
        'issued_at'              => 'datetime',
        'due_at'                 => 'datetime',
        'paid_at'                => 'datetime',
        'cancelled_at'           => 'datetime',
        'refunded_at'            => 'datetime',
    ];

    public function isCancelled(): bool { return $this->status === 'cancelled'; }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'issued'    => ['label' => 'Issued',    'color' => 'blue'],
            'paid'      => ['label' => 'Paid',      'color' => 'green'],
            'cancelled' => ['label' => 'Cancelled', 'color' => 'red'],
            'refunded'  => ['label' => 'Refunded',  'color' => 'purple'],
            default     => ['label' => 'Draft',     'color' => 'gray'],
        };
    }

    public static function generateInvoiceNumber(): string
    {
        $year   = now()->format('Y');
        $prefix = "INV-{$year}-";

        // Extract the highest existing sequence for this year and increment it.
        // Using MAX on the numeric suffix avoids the count() race condition where
        // two concurrent inserts both see the same count and collide on the unique key.
        $last = static::where('invoice_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->max('invoice_number');

        $next = $last
            ? (int) substr($last, strlen($prefix)) + 1
            : 1;

        return $prefix . str_pad($next, 6, '0', STR_PAD_LEFT);
    }
}
