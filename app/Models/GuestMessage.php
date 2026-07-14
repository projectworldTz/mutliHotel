<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestMessage extends Model
{
    const SENDER_GUEST = 'guest';
    const SENDER_STAFF  = 'staff';

    protected $fillable = [
        'hotel_id', 'booking_id', 'sender_id', 'sender_type', 'message', 'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function hotel(): BelongsTo   { return $this->belongsTo(Hotel::class); }
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function sender(): BelongsTo  { return $this->belongsTo(User::class, 'sender_id'); }

    public function scopeForHotel($query, int $hotelId)     { return $query->where('hotel_id', $hotelId); }
    public function scopeForBooking($query, int $bookingId) { return $query->where('booking_id', $bookingId); }
    public function scopeUnread($query)                     { return $query->whereNull('read_at'); }
    public function scopeFromGuest($query)                  { return $query->where('sender_type', self::SENDER_GUEST); }
}
