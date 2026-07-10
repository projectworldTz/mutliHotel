<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __construct(private AvailabilityService $availabilityService) {}

    public function index(Request $request)
    {
        /** @var Hotel $hotel */
        $hotel     = $request->attributes->get('assigned_hotel');
        $roomTypes = RoomType::where('hotel_id', $hotel->id)->with('rooms')->get();

        $from = $request->input('from', now()->toDateString());
        $to   = $request->input('to', now()->addDays(14)->toDateString());

        $grid = $roomTypes->map(function (RoomType $rt) use ($from, $to) {
            $calendar = $this->availabilityService->detailedCalendarForRoomType($rt, now()->year, now()->month);
            return [
                'room_type' => $rt,
                'calendar'  => $calendar,
                'total'     => $rt->rooms->count(),
            ];
        });

        return view('receptionist.availability', compact('hotel', 'grid', 'from', 'to', 'roomTypes'));
    }

    /** AJAX: detailed (available/partial/booked) calendar for a room type in a given month. */
    public function calendar(Request $request, RoomType $roomType, int $year, int $month)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_if($roomType->hotel_id !== $hotel->id, 404);

        $calendar = $this->availabilityService->detailedCalendarForRoomType($roomType, $year, $month);

        return response()->json(['calendar' => $calendar, 'year' => $year, 'month' => $month]);
    }
}
