<?php

namespace App\Repositories;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomAvailability;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Collection;

class RoomRepository
{
    // ── Room type lookups ─────────────────────────────────────────────────────

    public function roomTypesForHotel(Hotel $hotel): Collection
    {
        return RoomType::where('hotel_id', $hotel->id)
            ->active()
            ->with(['images', 'amenities'])
            ->get();
    }

    public function findRoomType(int $id): ?RoomType
    {
        return RoomType::with(['hotel', 'images', 'amenities', 'seasonalPrices'])->find($id);
    }

    public function findRoomTypeBySlug(Hotel $hotel, string $slug): ?RoomType
    {
        return RoomType::where('hotel_id', $hotel->id)
            ->where('slug', $slug)
            ->with(['images', 'amenities'])
            ->first();
    }

    // ── Room lookups ──────────────────────────────────────────────────────────

    public function findRoom(int $id): ?Room
    {
        return Room::with(['hotel', 'roomType'])->find($id);
    }

    public function roomsForHotel(Hotel $hotel): Collection
    {
        return Room::where('hotel_id', $hotel->id)
            ->with(['roomType'])
            ->orderBy('room_number')
            ->get();
    }

    public function roomsForType(RoomType $roomType): Collection
    {
        return Room::where('room_type_id', $roomType->id)
            ->available()
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get();
    }

    // ── Availability-aware queries ────────────────────────────────────────────

    /**
     * Return all physically available rooms of a type that have no blocking
     * records in the requested stay window.
     */
    public function availableRoomsForType(RoomType $roomType, string $checkIn, string $checkOut): Collection
    {
        $allIds = Room::where('room_type_id', $roomType->id)
            ->available()
            ->pluck('id')
            ->toArray();

        if (empty($allIds)) {
            return new Collection();
        }

        $freeIds = RoomAvailability::filterAvailableRooms($allIds, $checkIn, $checkOut);

        if (empty($freeIds)) {
            return new Collection();
        }

        return Room::whereIn('id', $freeIds)->with(['roomType'])->get();
    }

    /**
     * Find the first available room of a type for the stay window.
     * Returns null when fully booked.
     */
    public function firstAvailableRoom(RoomType $roomType, string $checkIn, string $checkOut): ?Room
    {
        return $this->availableRoomsForType($roomType, $checkIn, $checkOut)->first();
    }

    /**
     * Count available rooms per room type for a hotel across a stay window.
     * Returns [ roomTypeId => count, ... ].
     */
    public function availableCountPerType(Hotel $hotel, string $checkIn, string $checkOut): array
    {
        $roomTypes = RoomType::where('hotel_id', $hotel->id)->active()->pluck('id')->toArray();
        $result    = [];

        foreach ($roomTypes as $typeId) {
            $allIds  = Room::where('room_type_id', $typeId)->available()->pluck('id')->toArray();
            $freeIds = RoomAvailability::filterAvailableRooms($allIds, $checkIn, $checkOut);
            $result[$typeId] = count($freeIds);
        }

        return $result;
    }
}
