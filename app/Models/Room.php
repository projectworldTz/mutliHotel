<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['hotel_id', 'room_type_id', 'room_number', 'floor', 'status'];

    protected $casts = [
        'floor' => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function availability()
    {
        return $this->hasMany(RoomAvailability::class);
    }

    public function bookingRooms()
    {
        return $this->hasMany(BookingRoom::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeByType($query, int $roomTypeId)
    {
        return $query->where('room_type_id', $roomTypeId);
    }

    // ── Availability helpers ──────────────────────────────────────────────────

    /**
     * Check if this specific room is free for every night in the stay range.
     * Uses the room_availability table for bookings and manual blocks.
     */
    public function isAvailableForDates(string $checkIn, string $checkOut): bool
    {
        return ! $this->availability()
            ->whereIn('status', ['booked', 'blocked'])
            ->where('date', '>=', $checkIn)
            ->where('date', '<', $checkOut)
            ->exists();
    }

    /**
     * Block all dates for a booking (called when booking is confirmed).
     */
    public function blockForBooking(Booking $booking): void
    {
        $start = \Carbon\Carbon::parse($booking->check_in);
        $end   = \Carbon\Carbon::parse($booking->check_out);

        $dates = [];
        while ($start->lt($end)) {
            $dates[] = [
                'room_id'    => $this->id,
                'booking_id' => $booking->id,
                'date'       => $start->toDateString(),
                'status'     => 'booked',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $start->addDay();
        }

        RoomAvailability::upsert($dates, ['room_id', 'date'], ['booking_id', 'status', 'updated_at']);
    }

    /**
     * Release dates back to available (called on cancellation).
     */
    public function releaseBooking(Booking $booking): void
    {
        $this->availability()
            ->where('booking_id', $booking->id)
            ->delete();
    }
}
