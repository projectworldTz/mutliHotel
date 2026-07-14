<?php

namespace App\Http\Controllers;

use App\Enums\Feature;
use App\Models\Booking;
use App\Models\DigitalCheckin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckinController extends Controller
{
    /**
     * Guest submits (or updates, before verification) their pre-arrival
     * digital check-in for their own booking.
     */
    public function store(string $bookingNumber, Request $request)
    {
        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();
        abort_unless($booking->user_id === Auth::id(), 403);
        abort_unless($booking->hotel->hasFeature(Feature::DIGITAL_CHECKIN), 403);

        $existing = DigitalCheckin::where('booking_id', $booking->id)->first();
        abort_if($existing?->isVerified(), 403, 'Your check-in has already been verified by the hotel.');

        $data = $request->validate([
            'estimated_arrival_time' => ['required', 'date_format:H:i'],
            'preferences'            => ['nullable', 'string', 'max:1000'],
            'id_document'            => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $path = $existing?->id_document_path;
        if ($request->hasFile('id_document')) {
            $path = $request->file('id_document')->store("checkin-documents/{$booking->id}", 'public');
        }

        DigitalCheckin::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'estimated_arrival_time' => $data['estimated_arrival_time'],
                'preferences'            => $data['preferences'] ?? null,
                'id_document_path'       => $path,
                'submitted_at'           => now(),
            ]
        );

        return back()->with('success', 'Your check-in details have been submitted.');
    }
}
