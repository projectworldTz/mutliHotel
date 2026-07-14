<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupBooking extends Model
{
    const STATUS_INQUIRY   = 'inquiry';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'hotel_id', 'event_name', 'organizer_name', 'organizer_email', 'organizer_phone',
        'event_start', 'event_end', 'rooms_requested', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'event_start' => 'date',
        'event_end'   => 'date',
    ];

    public function hotel(): BelongsTo   { return $this->belongsTo(Hotel::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeForHotel($query, int $hotelId) { return $query->where('hotel_id', $hotelId); }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_INQUIRY   => 'amber',
            self::STATUS_CONFIRMED => 'blue',
            self::STATUS_COMPLETED => 'emerald',
            self::STATUS_CANCELLED => 'rose',
            default                => 'slate',
        };
    }
}
