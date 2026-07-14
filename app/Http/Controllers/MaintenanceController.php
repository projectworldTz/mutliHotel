<?php

namespace App\Http\Controllers;

use App\Enums\Feature;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{
    /**
     * Guest reports a maintenance issue for their own booking.
     */
    public function store(string $bookingNumber, Request $request)
    {
        $booking = \App\Models\Booking::where('booking_number', $bookingNumber)->firstOrFail();
        abort_unless($booking->user_id === Auth::id(), 403);
        abort_unless($booking->hotel->hasFeature(Feature::MAINTENANCE_REQUESTS), 403);

        $data = $request->validate([
            'category'    => ['required', 'in:plumbing,electrical,hvac,furniture,appliance,other'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        MaintenanceRequest::create($data + [
            'hotel_id'    => $booking->hotel_id,
            'booking_id'  => $booking->id,
            'room_id'     => $booking->rooms->first()?->room_id,
            'reported_by' => Auth::id(),
            'priority'    => MaintenanceRequest::PRIORITY_NORMAL,
            'status'      => MaintenanceRequest::STATUS_PENDING,
        ]);

        return back()->with('success', 'Thanks — we\'ve notified the front desk about this issue.');
    }
}
