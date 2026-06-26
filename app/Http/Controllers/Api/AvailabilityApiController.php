<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckAvailabilityRequest;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Services\AvailabilityService;

class AvailabilityApiController extends Controller
{
    public function __construct(private AvailabilityService $availabilityService) {}

    /**
     * Check all available room types for a hotel for given dates + guests.
     */
    public function check(Hotel $hotel, CheckAvailabilityRequest $request)
    {
        abort_if($hotel->status !== 'active', 404);

        $results = $this->availabilityService->availableRoomTypes(
            $hotel,
            $request->check_in,
            $request->check_out,
            (int) $request->guests
        );

        return response()->json([
            'hotel_id'   => $hotel->id,
            'hotel_name' => $hotel->name,
            'check_in'   => $request->check_in,
            'check_out'  => $request->check_out,
            'guests'     => (int) $request->guests,
            'available'  => ! empty($results),
            'room_types' => array_map(fn ($r) => [
                'id'              => $r['room_type']->id,
                'name'            => $r['room_type']->name,
                'slug'            => $r['room_type']->slug,
                'description'     => $r['room_type']->description,
                'max_guests'      => $r['room_type']->max_guests,
                'bed_type'        => $r['room_type']->bed_type,
                'beds_count'      => $r['room_type']->beds_count,
                'size_sqm'        => $r['room_type']->size_sqm,
                'view_type'       => $r['room_type']->view_type,
                'smoking'         => $r['room_type']->smoking,
                'available_count' => $r['available_count'],
                'nightly_rate'    => $r['pricing']['nightly_rate'],
                'nights'          => $r['pricing']['nights'],
                'subtotal'        => $r['pricing']['subtotal'],
                'image_url'       => $r['room_type']->featured_image_url,
                'amenities'       => $r['room_type']->amenities?->pluck('name') ?? [],
            ], $results),
        ]);
    }

    /**
     * Calendar data for a specific room type in a given year/month.
     * Used by the React booking calendar component.
     */
    public function calendar(Hotel $hotel, RoomType $roomType, int $year, int $month)
    {
        abort_if($roomType->hotel_id !== $hotel->id, 404);

        $calendar = $this->availabilityService->calendarForRoomType($roomType, $year, $month);

        return response()->json([
            'room_type_id' => $roomType->id,
            'year'         => $year,
            'month'        => $month,
            'calendar'     => $calendar,
        ]);
    }
}
