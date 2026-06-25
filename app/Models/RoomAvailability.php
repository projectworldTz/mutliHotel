<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomAvailability extends Model
{
    use HasFactory;

    protected $table = 'room_availability';

    protected $fillable = ['room_id', 'booking_id', 'date', 'status', 'notes'];

    protected $casts = [
        'date' => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeBooked($query)
    {
        return $query->where('status', 'booked');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    public function scopeUnavailable($query)
    {
        return $query->whereIn('status', ['booked', 'blocked']);
    }

    public function scopeInRange($query, string $from, string $to)
    {
        return $query->where('date', '>=', $from)->where('date', '<', $to);
    }

    // ── Static helpers ────────────────────────────────────────────────────────

    /**
     * Check if a given room has NO blocking records in the date range.
     */
    public static function isRoomFree(int $roomId, string $checkIn, string $checkOut): bool
    {
        return ! static::where('room_id', $roomId)
            ->whereIn('status', ['booked', 'blocked'])
            ->where('date', '>=', $checkIn)
            ->where('date', '<', $checkOut)
            ->exists();
    }

    /**
     * Return IDs of all rooms from a given set that are free for the range.
     */
    public static function filterAvailableRooms(array $roomIds, string $checkIn, string $checkOut): array
    {
        $blockedRoomIds = static::whereIn('room_id', $roomIds)
            ->whereIn('status', ['booked', 'blocked'])
            ->where('date', '>=', $checkIn)
            ->where('date', '<', $checkOut)
            ->pluck('room_id')
            ->unique()
            ->toArray();

        return array_values(array_diff($roomIds, $blockedRoomIds));
    }
}
