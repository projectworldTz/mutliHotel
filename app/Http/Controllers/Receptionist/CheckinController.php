<?php

namespace App\Http\Controllers\Receptionist;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\DigitalCheckin;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function index(Request $request)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($hotel->hasFeature(Feature::DIGITAL_CHECKIN), 403,
            'Digital Check-in is not enabled for this hotel. Contact your hotel owner.'
        );

        $checkins = DigitalCheckin::whereHas('booking', fn ($q) => $q->where('hotel_id', $hotel->id)->whereIn('status', ['pending', 'confirmed']))
            ->with('booking.user')
            ->latest('submitted_at')
            ->paginate(30);

        return view('receptionist.checkins.index', compact('hotel', 'checkins'));
    }

    public function verify(Request $request, DigitalCheckin $checkin)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($checkin->booking->hotel_id === $hotel->id, 403);

        $checkin->update(['verified_at' => now(), 'verified_by' => auth()->id()]);

        return back()->with('success', 'Check-in verified.');
    }
}
