<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class BookingApiController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private PaymentService $paymentService,
    ) {}

    public function index(Request $request)
    {
        $perPage  = (int) $request->input('per_page', 10);
        $bookings = $this->bookingService->getUserBookings(auth()->user(), $perPage);

        return response()->json([
            'data' => $bookings->items(),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page'    => $bookings->lastPage(),
                'total'        => $bookings->total(),
            ],
        ]);
    }

    public function store(StoreBookingRequest $request)
    {
        try {
            $booking = $this->bookingService->createFromCart(auth()->user(), $request->validated());
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $payment = $this->paymentService->initiate(
            $booking,
            $request->payment_method,
            $request->only(['stripe_token', 'paypal_order_id'])
        );

        return response()->json([
            'success'        => true,
            'booking_number' => $booking->booking_number,
            'status'         => $booking->status,
            'payment'        => $payment,
        ], 201);
    }

    public function show(string $bookingNumber)
    {
        $booking = $this->bookingService->findByNumber($bookingNumber);

        abort_unless(
            $booking->user_id === auth()->id() || auth()->user()->isSuperAdmin(),
            403
        );

        $booking->loadMissing(['hotel.images', 'rooms.roomType', 'payment', 'invoice']);

        return response()->json(['data' => $booking]);
    }

    public function cancel(string $bookingNumber, Request $request)
    {
        $booking = $this->bookingService->findByNumber($bookingNumber);

        abort_unless($booking->user_id === auth()->id(), 403);

        try {
            $this->bookingService->cancel($booking, $request->input('reason', ''));
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => "Booking #{$booking->booking_number} cancelled."]);
    }
}
