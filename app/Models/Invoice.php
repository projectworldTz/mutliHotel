<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'invoice_number',
        'subtotal',
        'tax_total',
        'discount_total',
        'grand_total',
        'currency',
        'status',
        'issued_at',
        'due_at',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'subtotal'       => 'decimal:2',
        'tax_total'      => 'decimal:2',
        'discount_total' => 'decimal:2',
        'grand_total'    => 'decimal:2',
        'issued_at'      => 'datetime',
        'due_at'         => 'datetime',
        'paid_at'        => 'datetime',
    ];

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

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'issued'    => ['label' => 'Issued',    'color' => 'blue'],
            'paid'      => ['label' => 'Paid',      'color' => 'green'],
            'cancelled' => ['label' => 'Cancelled', 'color' => 'red'],
            default     => ['label' => 'Draft',     'color' => 'gray'],
        };
    }

    public static function generateInvoiceNumber(): string
    {
        $year     = now()->format('Y');
        $sequence = str_pad((static::whereYear('created_at', $year)->count() + 1), 6, '0', STR_PAD_LEFT);

        return "INV-{$year}-{$sequence}";
    }
}
