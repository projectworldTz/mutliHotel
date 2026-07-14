<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SatisfactionSurvey extends Model
{
    protected $fillable = [
        'hotel_id', 'booking_id', 'user_id', 'token',
        'rating', 'comment', 'sent_at', 'responded_at',
    ];

    protected $casts = [
        'rating'       => 'integer',
        'sent_at'      => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function hotel(): BelongsTo   { return $this->belongsTo(Hotel::class); }
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }

    public function scopeForHotel($query, int $hotelId) { return $query->where('hotel_id', $hotelId); }
    public function scopeResponded($query)               { return $query->whereNotNull('responded_at'); }

    public function isResponded(): bool
    {
        return $this->responded_at !== null;
    }

    public static function generateToken(): string
    {
        return Str::random(48);
    }
}
