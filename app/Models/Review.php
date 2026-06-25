<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'booking_id',
        'user_id',
        'product_id',   // legacy – nullable, kept until Phase 11 cleanup
        'rating',
        'title',
        'comment',
        'response',
        'responded_at',
        'approved',
    ];

    protected $casts = [
        'approved'     => 'boolean',
        'rating'       => 'integer',
        'responded_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('approved', false);
    }

    public function scopeForHotel($query, int $hotelId)
    {
        return $query->where('hotel_id', $hotelId);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function hasResponse(): bool
    {
        return ! empty($this->response);
    }

    public function getRatingStarsAttribute(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}
