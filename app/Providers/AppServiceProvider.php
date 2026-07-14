<?php

namespace App\Providers;

use App\Events\BookingCancelled;
use App\Events\BookingCheckedOut;
use App\Events\BookingCreated;
use App\Events\HotelSubmitted;
use App\Listeners\SendBookingCancellationEmail;
use App\Listeners\SendBookingConfirmationEmail;
use App\Listeners\SendGuestSurveyEmail;
use App\Listeners\SendHotelSubmittedNotification;
use App\Models\Hotel;
use App\Models\User;
use App\Repositories\AvailabilityRepository;
use App\Repositories\BookingRepository;
use App\Repositories\HotelRepository;
use App\Repositories\RoomRepository;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\CancellationService;
use App\Services\HotelService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\Payments\AirtelMoneyGateway;
use App\Services\Payments\DpoPayGateway;
use App\Services\Payments\HalotelGateway;
use App\Services\Payments\MixByYasGateway;
use App\Services\Payments\MpesaGateway;
use App\Services\PricingService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── Repositories (stateless — singletons are fine) ────────────────────
        $this->app->singleton(HotelRepository::class);
        $this->app->singleton(BookingRepository::class);
        $this->app->singleton(RoomRepository::class);
        $this->app->singleton(AvailabilityRepository::class);

        // ── Services (dependency graph resolved by the container) ─────────────
        $this->app->singleton(PricingService::class);
        $this->app->singleton(InvoiceService::class);

        $this->app->singleton(AvailabilityService::class, function ($app) {
            return new AvailabilityService(
                $app->make(RoomRepository::class),
                $app->make(AvailabilityRepository::class),
                $app->make(PricingService::class),
            );
        });

        $this->app->singleton(HotelService::class, function ($app) {
            return new HotelService(
                $app->make(HotelRepository::class),
            );
        });

        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService(
                $app->make(AirtelMoneyGateway::class),
                $app->make(MpesaGateway::class),
                $app->make(HalotelGateway::class),
                $app->make(MixByYasGateway::class),
                $app->make(DpoPayGateway::class),
            );
        });

        $this->app->singleton(CancellationService::class, function ($app) {
            return new CancellationService(
                $app->make(AvailabilityRepository::class),
            );
        });

        $this->app->singleton(BookingService::class, function ($app) {
            return new BookingService(
                $app->make(BookingRepository::class),
                $app->make(RoomRepository::class),
                $app->make(AvailabilityRepository::class),
                $app->make(AvailabilityService::class),
                $app->make(PricingService::class),
                $app->make(InvoiceService::class),
                $app->make(CancellationService::class),
            );
        });

    }

    public function boot(): void
    {
        // ── Gates ─────────────────────────────────────────────────────────────
        Gate::define('access-admin', fn (User $user) => $user->isSuperAdmin());
        Gate::define('access-owner', fn (User $user) => $user->isHotelOwner() || $user->isSuperAdmin());
        Gate::define('manage-hotel', fn (User $user, Hotel $hotel) =>
            $user->isSuperAdmin() || $hotel->isOwnedBy($user)
        );
        Gate::define('access-staff', fn (User $user) =>
            $user->hasAnyRole(['receptionist', 'manager', 'cashier'])
        );

        // ── Event → Listener wiring ───────────────────────────────────────────
        Event::listen(BookingCreated::class,    SendBookingConfirmationEmail::class);
        Event::listen(BookingCancelled::class,  SendBookingCancellationEmail::class);
        Event::listen(HotelSubmitted::class,    SendHotelSubmittedNotification::class);
        Event::listen(BookingCheckedOut::class, SendGuestSurveyEmail::class);

    }
}
