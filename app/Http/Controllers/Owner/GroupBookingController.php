<?php

namespace App\Http\Controllers\Owner;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\GroupBooking;
use App\Models\Hotel;
use Illuminate\Http\Request;

class GroupBookingController extends Controller
{
    public function index(Hotel $hotel)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($hotel->hasFeature(Feature::GROUP_BOOKING), 403,
            'Group Booking Manager is not enabled for this hotel.'
        );

        $groupBookings = GroupBooking::forHotel($hotel->id)->latest('event_start')->get();

        return view('owner.group-bookings.index', compact('hotel', 'groupBookings'));
    }

    public function store(Hotel $hotel, Request $request)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($hotel->hasFeature(Feature::GROUP_BOOKING), 403);

        $data = $this->validated($request);
        $data['hotel_id']   = $hotel->id;
        $data['created_by'] = auth()->id();

        GroupBooking::create($data);

        return back()->with('success', 'Group booking added.');
    }

    public function update(Hotel $hotel, GroupBooking $groupBooking, Request $request)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($groupBooking->hotel_id === $hotel->id, 403);

        $groupBooking->update($this->validated($request));

        return back()->with('success', 'Group booking updated.');
    }

    public function destroy(Hotel $hotel, GroupBooking $groupBooking)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($groupBooking->hotel_id === $hotel->id, 403);

        $groupBooking->delete();

        return back()->with('success', 'Group booking removed.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'event_name'      => ['required', 'string', 'max:150'],
            'organizer_name'  => ['required', 'string', 'max:150'],
            'organizer_email' => ['nullable', 'email', 'max:255'],
            'organizer_phone' => ['nullable', 'string', 'max:30'],
            'event_start'     => ['required', 'date'],
            'event_end'       => ['required', 'date', 'after_or_equal:event_start'],
            'rooms_requested' => ['required', 'integer', 'min:1'],
            'status'          => ['required', 'in:inquiry,confirmed,completed,cancelled'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
