<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomAvailability;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AvailabilityRepository
{
    /**
     * Return all blocked/booked date records for a room in a given range.
     * Used to build calendar views (returns dates only, not full records).
     */
    public function unavailableDatesForRoom(int $roomId, string $from, string $to): Collection
    {
        return RoomAvailability::where('room_id', $roomId)
            ->unavailable()
            ->inRange($from, $to)
            ->get();
    }

    /**
     * Return how many rooms of a given type are blocked, per date, in a range.
     * Used to distinguish "no rooms booked" / "some rooms booked" / "fully booked".
     */
    public function blockedCountsForType(int $roomTypeId, string $from, string $to): array
    {
        $roomIds = Room::where('room_type_id', $roomTypeId)->available()->pluck('id')->toArray();

        if (empty($roomIds)) {
            return [];
        }

        return RoomAvailability::whereIn('room_id', $roomIds)
            ->unavailable()
            ->inRange($from, $to)
            ->selectRaw('date, COUNT(DISTINCT room_id) as blocked_count')
            ->groupBy('date')
            ->pluck('blocked_count', 'date')
            ->toArray();
    }

    /**
     * Return all dates that are blocked across ALL rooms of a given room type.
     * A date is "fully blocked" only when every room of the type is blocked on that date.
     */
    public function fullyBlockedDatesForType(int $roomTypeId, string $from, string $to): array
    {
        $roomIds = Room::where('room_type_id', $roomTypeId)->available()->pluck('id')->toArray();

        if (empty($roomIds)) {
            return [];
        }

        $totalRooms = count($roomIds);

        // Count how many rooms are blocked per date
        $blockedCounts = RoomAvailability::whereIn('room_id', $roomIds)
            ->unavailable()
            ->inRange($from, $to)
            ->selectRaw('date, COUNT(DISTINCT room_id) as blocked_count')
            ->groupBy('date')
            ->pluck('blocked_count', 'date')
            ->toArray();

        // A date is fully sold out only if blocked_count equals total rooms
        return array_keys(array_filter($blockedCounts, fn ($count) => $count >= $totalRooms));
    }

    /**
     * Persist availability blocks when a booking is confirmed.
     */
    public function blockForBooking(Room $room, Booking $booking): void
    {
        $room->blockForBooking($booking);
    }

    /**
     * Release availability records for a cancelled booking.
     */
    public function releaseForBooking(Room $room, Booking $booking): void
    {
        $room->releaseBooking($booking);
    }

    /**
     * Release all rooms attached to a booking at once (for cancellations).
     */
    public function releaseAllForBooking(Booking $booking): void
    {
        RoomAvailability::where('booking_id', $booking->id)->delete();
    }

    /**
     * Find the earliest date a room of this type becomes free again,
     * starting from $fromDate. Returns null if no active bookings exist.
     *
     * Strategy: join room_availability → bookings and find the minimum
     * check_out date of any active booking that blocks a room of this type
     * on or after $fromDate. That check_out date is when the first room
     * becomes free again.
     */
    public function nextAvailableDateForType(RoomType $roomType, string $fromDate): ?string
    {
        $roomIds = Room::where('room_type_id', $roomType->id)
            ->where('status', 'available')
            ->pluck('id')
            ->toArray();

        if (empty($roomIds)) {
            return null;
        }

        $minCheckout = DB::table('room_availability')
            ->join('bookings', 'room_availability.booking_id', '=', 'bookings.id')
            ->whereIn('room_availability.room_id', $roomIds)
            ->whereIn('room_availability.status', ['booked', 'blocked'])
            ->where('room_availability.date', '>=', $fromDate)
            ->whereNotIn('bookings.status', [Booking::STATUS_CANCELLED])
            ->min('bookings.check_out');

        return $minCheckout;
    }
}
