<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var Hotel $hotel */
        $hotel = $request->attributes->get('assigned_hotel');
        $today = now()->toDateString();

        $arrivalsToday = Booking::where('hotel_id', $hotel->id)
            ->where('check_in', $today)
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_CHECKED_IN])
            ->with(['user', 'rooms.roomType'])
            ->get();

        $departuresToday = Booking::where('hotel_id', $hotel->id)
            ->where('check_out', $today)
            ->where('status', Booking::STATUS_CHECKED_IN)
            ->with(['user', 'rooms.roomType'])
            ->get();

        $pendingConfirmation = Booking::where('hotel_id', $hotel->id)
            ->where('status', Booking::STATUS_PENDING)
            ->count();

        $currentlyCheckedIn = Booking::where('hotel_id', $hotel->id)
            ->where('status', Booking::STATUS_CHECKED_IN)
            ->count();

        $stats = [
            'arrivals_today'      => $arrivalsToday->count(),
            'departures_today'    => $departuresToday->count(),
            'pending_confirm'     => $pendingConfirmation,
            'currently_in'        => $currentlyCheckedIn,
        ];

        return view('receptionist.dashboard', compact(
            'hotel', 'stats', 'arrivalsToday', 'departuresToday'
        ));
    }
}
