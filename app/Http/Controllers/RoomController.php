<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\RoomType;
use App\Services\AvailabilityService;
use App\Services\PricingService;

class RoomController extends Controller
{
    public function __construct(
        private AvailabilityService $availabilityService,
        private PricingService      $pricingService,
    ) {}

    /**
     * Room type detail page for a specific hotel.
     */
    public function show(Hotel $hotel, RoomType $roomType)
    {
        abort_if($hotel->status !== 'active', 404);
        abort_if($roomType->hotel_id !== $hotel->id, 404);

        $roomType->loadMissing(['images', 'amenities', 'hotel']);

        // Calendar data for the current month (for the React calendar component)
        $calendar = $this->availabilityService->calendarForRoomType(
            $roomType,
            now()->year,
            now()->month
        );

        return view('hotels.room', compact('hotel', 'roomType', 'calendar'));
    }

    /**
     * AJAX: calendar data for a room type in a given month.
     * Used by the React booking calendar.
     */
    public function calendar(Hotel $hotel, RoomType $roomType, int $year, int $month)
    {
        abort_if($roomType->hotel_id !== $hotel->id, 404);

        $calendar = $this->availabilityService->calendarForRoomType($roomType, $year, $month);

        return response()->json(['calendar' => $calendar, 'year' => $year, 'month' => $month]);
    }
}
