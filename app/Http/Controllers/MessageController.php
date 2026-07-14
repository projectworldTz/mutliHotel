<?php

namespace App\Http\Controllers;

use App\Enums\Feature;
use App\Models\Booking;
use App\Models\GuestMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Guest sends a message to the front desk for their own booking.
     */
    public function store(string $bookingNumber, Request $request)
    {
        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();
        abort_unless($booking->user_id === Auth::id(), 403);
        abort_unless($booking->hotel->hasFeature(Feature::GUEST_MESSAGING), 403);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        GuestMessage::create([
            'hotel_id'    => $booking->hotel_id,
            'booking_id'  => $booking->id,
            'sender_id'   => Auth::id(),
            'sender_type' => GuestMessage::SENDER_GUEST,
            'message'     => $data['message'],
        ]);

        return back()->with('success', 'Message sent.');
    }
}
