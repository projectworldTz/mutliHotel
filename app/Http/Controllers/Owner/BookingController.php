<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    public function index(Hotel $hotel, Request $request)
    {
        $this->authorizeHotel($hotel);

        $filters  = $request->only(['status', 'date_from', 'date_to', 'search']);
        $bookings = $this->bookingService->getHotelBookings($hotel, $filters, 20);
        $stats    = $this->bookingService->hotelStats($hotel);

        return view('owner.bookings.index', compact('hotel', 'bookings', 'filters', 'stats'));
    }

    public function show(Hotel $hotel, Booking $booking)
    {
        $this->authorizeHotel($hotel);
        abort_unless($booking->hotel_id === $hotel->id, 404);

        $booking->loadMissing(['user', 'rooms.roomType', 'payment', 'invoice']);

        return view('owner.bookings.show', compact('hotel', 'booking'));
    }

    public function confirm(Hotel $hotel, Booking $booking)
    {
        $this->authorizeHotel($hotel);
        abort_unless($booking->hotel_id === $hotel->id, 404);

        $this->bookingService->confirm($booking);

        return back()->with('success', "Booking #{$booking->booking_number} confirmed.");
    }

    public function checkIn(Hotel $hotel, Booking $booking)
    {
        $this->authorizeHotel($hotel);
        abort_unless($booking->hotel_id === $hotel->id, 404);

        $this->bookingService->checkIn($booking);

        return back()->with('success', "Guest checked in for booking #{$booking->booking_number}.");
    }

    public function checkOut(Hotel $hotel, Booking $booking)
    {
        $this->authorizeHotel($hotel);
        abort_unless($booking->hotel_id === $hotel->id, 404);

        $this->bookingService->checkOut($booking);

        return back()->with('success', "Guest checked out for booking #{$booking->booking_number}.");
    }

    public function cancel(Hotel $hotel, Booking $booking, Request $request)
    {
        $this->authorizeHotel($hotel);
        abort_unless($booking->hotel_id === $hotel->id, 404);

        try {
            $this->bookingService->cancel($booking, $request->input('reason', 'Cancelled by hotel.'));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['cancel' => $e->getMessage()]);
        }

        return back()->with('success', "Booking #{$booking->booking_number} cancelled.");
    }

    private function authorizeHotel(Hotel $hotel): void
    {
        abort_unless(
            auth()->user()->isSuperAdmin() || $hotel->isOwnedBy(auth()->user()),
            403
        );
    }
}
