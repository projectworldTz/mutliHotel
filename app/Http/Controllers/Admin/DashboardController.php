<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Services\BookingService;
use App\Services\HotelService;

class DashboardController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private HotelService   $hotelService,
    ) {}

    public function index()
    {
        $hotelStats   = $this->hotelService->stats();
        $bookingStats = $this->bookingService->platformStats();
        $revenue      = $this->bookingService->revenueByMonth(12);

        // Derived stats expected by the dashboard view
        $bookingStats['active'] = ($bookingStats['pending'] ?? 0)
                                + ($bookingStats['confirmed'] ?? 0)
                                + ($bookingStats['checked_in'] ?? 0);

        $bookingStats['revenue_month'] = Booking::whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CHECKED_IN,
                Booking::STATUS_CHECKED_OUT,
            ])
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('grand_total');

        $recentBookings = Booking::with(['user', 'hotel'])
            ->latest()
            ->take(10)
            ->get();

        $pendingHotels = Hotel::where('status', 'pending')
            ->with('owner')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'hotelStats', 'bookingStats', 'revenue', 'recentBookings', 'pendingHotels'
        ));
    }
}
