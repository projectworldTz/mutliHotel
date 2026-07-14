<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    const TYPE_EXPENSE = 'expense';
    const TYPE_PAYOUT   = 'payout';

    protected $fillable = [
        'hotel_id', 'type', 'category', 'payee',
        'description', 'amount', 'expense_date',
        'created_by', 'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function hotel(): BelongsTo   { return $this->belongsTo(Hotel::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForHotel($query, int $hotelId) { return $query->where('hotel_id', $hotelId); }
    public function scopeType($query, string $type)      { return $query->where('type', $type); }
    public function scopeBetween($query, $from, $to)
    {
        return $query->whereBetween('expense_date', [$from, $to]);
    }
}
