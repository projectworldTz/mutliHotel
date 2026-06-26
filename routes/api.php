<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvailabilityApiController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\HotelApiController;
use App\Http\Controllers\Api\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

// ── Auth ─────────────────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// ── Public hotel endpoints (used by React search / live preview) ──────────────
Route::prefix('hotels')->group(function () {
    Route::get('/', [HotelApiController::class, 'index']);
    Route::get('/featured', [HotelApiController::class, 'featured']);
    Route::get('/{hotel}', [HotelApiController::class, 'show']);
    Route::get('/{hotel}/room-types', [HotelApiController::class, 'roomTypes']);

    // Availability (React availability checker + booking calendar)
    Route::get('/{hotel}/availability', [AvailabilityApiController::class, 'check']);
    Route::get('/{hotel}/room-types/{roomType}/calendar/{year}/{month}', [AvailabilityApiController::class, 'calendar'])
        ->whereNumber(['year', 'month']);
});

// ── Authenticated endpoints ───────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Reservation cart
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartApiController::class, 'index']);
        Route::post('/', [CartApiController::class, 'store']);
        Route::delete('/{item}', [CartApiController::class, 'destroy']);
        Route::post('/coupon', [CartApiController::class, 'applyCoupon']);
        Route::post('/preview', [CartApiController::class, 'preview']);
    });

    // Bookings
    Route::prefix('bookings')->group(function () {
        Route::get('/', [BookingApiController::class, 'index']);
        Route::post('/', [BookingApiController::class, 'store']);
        Route::get('/{bookingNumber}', [BookingApiController::class, 'show']);
        Route::post('/{bookingNumber}/cancel', [BookingApiController::class, 'cancel']);
    });
});

// ── Payment webhooks (no auth — verified by gateway signature) ────────────────
Route::prefix('webhooks')->group(function () {
    Route::post('/stripe', [PaymentWebhookController::class, 'stripe']);
    Route::post('/paypal', [PaymentWebhookController::class, 'paypal']);
});
