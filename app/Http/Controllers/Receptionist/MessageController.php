<?php

namespace App\Http\Controllers\Receptionist;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\GuestMessage;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($hotel->hasFeature(Feature::GUEST_MESSAGING), 403,
            'Guest Messaging is not enabled for this hotel. Contact your hotel owner.'
        );

        // One row per booking that has messages, latest message first, with an unread count.
        $bookingIds = GuestMessage::forHotel($hotel->id)
            ->select('booking_id')
            ->distinct()
            ->pluck('booking_id');

        $bookings = Booking::whereIn('id', $bookingIds)
            ->with(['user', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->withCount(['messages as unread_count' => fn ($q) => $q->unread()->fromGuest()])
            ->get()
            ->sortByDesc(fn ($b) => optional($b->messages->first())->created_at)
            ->values();

        return view('receptionist.messages.index', compact('hotel', 'bookings'));
    }

    public function show(Request $request, Booking $booking)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($booking->hotel_id === $hotel->id, 403);

        $booking->load('user');
        $messages = GuestMessage::forBooking($booking->id)->oldest()->get();

        GuestMessage::forBooking($booking->id)->fromGuest()->unread()->update(['read_at' => now()]);

        return view('receptionist.messages.show', compact('hotel', 'booking', 'messages'));
    }

    public function store(Request $request, Booking $booking)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($booking->hotel_id === $hotel->id, 403);

        $data = $request->validate(['message' => ['required', 'string', 'max:1000']]);

        GuestMessage::create([
            'hotel_id'    => $hotel->id,
            'booking_id'  => $booking->id,
            'sender_id'   => auth()->id(),
            'sender_type' => GuestMessage::SENDER_STAFF,
            'message'     => $data['message'],
        ]);

        return back()->with('success', 'Reply sent.');
    }
}
