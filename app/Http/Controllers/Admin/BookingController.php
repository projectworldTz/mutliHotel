<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    public function index(Request $request)
    {
        $filters  = $request->only(['status', 'date_from', 'date_to', 'search', 'hotel_id']);
        $bookings = $this->bookingService->allPaginated($filters);

        return view('admin.bookings.index', compact('bookings', 'filters'));
    }

    public function show(Booking $booking)
    {
        $booking->loadMissing([
            'user', 'hotel', 'rooms.roomType', 'payment', 'invoice',
        ]);

        return view('admin.bookings.show', compact('booking'));
    }

    public function confirm(Booking $booking)
    {
        $this->bookingService->confirm($booking);

        return back()->with('success', "Booking #{$booking->booking_number} confirmed.");
    }

    public function cancel(Booking $booking, Request $request)
    {
        try {
            $this->bookingService->cancel($booking, $request->input('reason', 'Cancelled by admin.'));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['cancel' => $e->getMessage()]);
        }

        return back()->with('success', "Booking #{$booking->booking_number} cancelled.");
    }

    public function checkIn(Booking $booking)
    {
        $this->bookingService->checkIn($booking);

        return back()->with('success', "Guest checked in for booking #{$booking->booking_number}.");
    }

    public function checkOut(Booking $booking)
    {
        $this->bookingService->checkOut($booking);

        return back()->with('success', "Guest checked out for booking #{$booking->booking_number}.");
    }
}
