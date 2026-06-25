<?php

namespace App\Providers;

use App\Repositories\AvailabilityRepository;
use App\Repositories\BookingRepository;
use App\Repositories\HotelRepository;
use App\Repositories\RoomRepository;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\HotelService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\Payments\BankTransferGateway;
use App\Services\Payments\CashGateway;
use App\Services\Payments\PayPalGateway;
use App\Services\Payments\StripeGateway;
use App\Services\PricingService;
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

        // ── Payment gateways ──────────────────────────────────────────────────
        $this->app->singleton(StripeGateway::class);
        $this->app->singleton(PayPalGateway::class);
        $this->app->singleton(BankTransferGateway::class);
        $this->app->singleton(CashGateway::class);

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
                $app->make(StripeGateway::class),
                $app->make(PayPalGateway::class),
                $app->make(BankTransferGateway::class),
                $app->make(CashGateway::class),
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
            );
        });

        // ── Legacy e-commerce bindings (kept until Phase 11 cleanup) ──────────
        $this->app->singleton(\App\Repositories\ProductRepository::class);
        $this->app->singleton(\App\Repositories\OrderRepository::class);
        $this->app->singleton(\App\Services\ProductService::class);
        $this->app->singleton(\App\Services\OrderService::class);
    }

    public function boot(): void
    {
        //
    }
}
