<?php

namespace App\Listeners;

use App\Events\HotelSubmitted;
use App\Mail\HotelSubmittedMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendHotelSubmittedNotification implements ShouldQueue
{
    public function handle(HotelSubmitted $event): void
    {
        $hotel = $event->hotel;
        $hotel->loadMissing('owner');

        $superAdmins = User::whereHas('roles', fn ($q) => $q->where('name', 'super-admin'))
            ->whereNotNull('email')
            ->get();

        foreach ($superAdmins as $admin) {
            Mail::to($admin->email)->send(new HotelSubmittedMail($hotel));
        }
    }

    public function failed(HotelSubmitted $event, \Throwable $exception): void
    {
        Log::error('Failed to send hotel-submitted notification', [
            'hotel' => $event->hotel->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
