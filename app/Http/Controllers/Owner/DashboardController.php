<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Services\BookingService;
use App\Services\HotelService;

class DashboardController extends Controller
{
    public function __construct(
        private HotelService   $hotelService,
        private BookingService $bookingService,
    ) {}

    public function index()
    {
        $owner  = auth()->user();
        $hotels = $this->hotelService->allForOwner($owner);

        $hotelIds = $hotels->pluck('id')->all();

        // Active bookings = pending + confirmed + checked_in
        $activeBookings = Booking::whereIn('hotel_id', $hotelIds)
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED, Booking::STATUS_CHECKED_IN])
            ->count();

        $revenueMonth = Booking::whereIn('hotel_id', $hotelIds)
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_CHECKED_IN, Booking::STATUS_CHECKED_OUT])
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('grand_total');

        $totalRooms = Room::whereIn('hotel_id', $hotelIds)->count();

        $stats = [
            'hotels'          => $hotels->count(),
            'active_bookings' => $activeBookings,
            'revenue_month'   => $revenueMonth,
            'rooms'           => $totalRooms,
        ];

        $recentBookings = Booking::whereIn('hotel_id', $hotelIds)
            ->with(['user', 'hotel'])
            ->latest()
            ->take(10)
            ->get();

        return view('owner.dashboard', compact('stats', 'hotels', 'recentBookings'));
    }
}
