<?php

namespace App\Listeners;

use App\Events\BookingCancelled;
use App\Mail\BookingCancelledMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendBookingCancellationEmail implements ShouldQueue
{
    public function handle(BookingCancelled $event): void
    {
        $booking = $event->booking;
        $booking->loadMissing(['user', 'hotel']);

        if ($booking->user?->email) {
            Mail::to($booking->user->email)
                ->send(new BookingCancelledMail($booking));
        }
    }

    public function failed(BookingCancelled $event, \Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error(
            'Failed to send booking cancellation email',
            ['booking' => $event->booking->booking_number, 'error' => $exception->getMessage()]
        );
    }
}
