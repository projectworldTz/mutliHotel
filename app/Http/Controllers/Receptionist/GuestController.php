<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        /** @var Hotel $hotel */
        $hotel  = $request->attributes->get('assigned_hotel');
        $search = $request->input('search');

        // Guests who have at least one booking at this hotel
        $query = User::whereHas('bookings', fn ($q) => $q->where('hotel_id', $hotel->id));

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $guests = $query->withCount([
            'bookings as total_stays' => fn ($q) => $q->where('hotel_id', $hotel->id),
        ])->latest()->paginate(25)->withQueryString();

        return view('receptionist.guests.index', compact('hotel', 'guests', 'search'));
    }

    public function show(Request $request, User $guest)
    {
        /** @var Hotel $hotel */
        $hotel = $request->attributes->get('assigned_hotel');

        // Confirm this guest has bookings at this hotel
        abort_unless(
            $guest->bookings()->where('hotel_id', $hotel->id)->exists(),
            403
        );

        $bookings = Booking::where('hotel_id', $hotel->id)
            ->where('user_id', $guest->id)
            ->with(['rooms.roomType'])
            ->latest()
            ->get();

        return view('receptionist.guests.show', compact('hotel', 'guest', 'bookings'));
    }
}
