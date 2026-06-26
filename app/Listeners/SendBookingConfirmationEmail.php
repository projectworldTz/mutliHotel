<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Mail\BookingConfirmedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmationEmail implements ShouldQueue
{
    public function handle(BookingCreated $event): void
    {
        $booking = $event->booking;
        $booking->loadMissing(['user', 'hotel', 'rooms.roomType', 'invoice']);

        if ($booking->user?->email) {
            Mail::to($booking->user->email)
                ->send(new BookingConfirmedMail($booking));
        }
    }

    public function failed(BookingCreated $event, \Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error(
            'Failed to send booking confirmation email',
            ['booking' => $event->booking->booking_number, 'error' => $exception->getMessage()]
        );
    }
}
