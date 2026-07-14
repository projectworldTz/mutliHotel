<?php

namespace App\Listeners;

use App\Enums\Feature;
use App\Events\BookingCheckedOut;
use App\Mail\GuestSurveyMail;
use App\Models\SatisfactionSurvey;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendGuestSurveyEmail implements ShouldQueue
{
    public function handle(BookingCheckedOut $event): void
    {
        $booking = $event->booking;
        $booking->loadMissing(['hotel', 'user']);

        if (! $booking->hotel->hasFeature(Feature::GUEST_SURVEYS) || ! $booking->user?->email) {
            return;
        }

        // One survey per booking — a re-dispatched event (e.g. retried job) must not spam the guest.
        if (SatisfactionSurvey::where('booking_id', $booking->id)->exists()) {
            return;
        }

        $survey = SatisfactionSurvey::create([
            'hotel_id'   => $booking->hotel_id,
            'booking_id' => $booking->id,
            'user_id'    => $booking->user_id,
            'token'      => SatisfactionSurvey::generateToken(),
            'sent_at'    => now(),
        ]);

        // Send a few hours after checkout rather than the instant they leave.
        Mail::to($booking->user->email)
            ->later(now()->addHours(3), new GuestSurveyMail($survey));
    }

    public function failed(BookingCheckedOut $event, \Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error(
            'Failed to send guest survey email',
            ['booking' => $event->booking->booking_number, 'error' => $exception->getMessage()]
        );
    }
}
