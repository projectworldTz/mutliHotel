<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\HotelController as AdminHotelController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Owner\CouponController as OwnerCouponController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingCartController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\Owner\BookingController as OwnerBookingController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboard;
use App\Http\Controllers\Owner\HotelController as OwnerHotelController;
use App\Http\Controllers\Owner\StaffController as OwnerStaffController;
use App\Http\Controllers\Receptionist\AvailabilityController as ReceptionistAvailability;
use App\Http\Controllers\Receptionist\BookingController as ReceptionistBookingController;
use App\Http\Controllers\Receptionist\DashboardController as ReceptionistDashboard;
use App\Http\Controllers\Receptionist\GuestController as ReceptionistGuestController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

// ── Language ─────────────────────────────────────────────────────────────────
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// ── Auth ─────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

    // Password reset
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── Public ───────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

// Blog (unchanged domain, keep existing routes)
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post}', [BlogController::class, 'show'])->name('blog.show');

// Hotel browsing
Route::prefix('hotels')->name('hotels.')->group(function () {
    Route::get('/', [HotelController::class, 'index'])->name('index');
    Route::get('/{hotel}', [HotelController::class, 'show'])->name('show');
    Route::get('/{hotel}/availability', [HotelController::class, 'availability'])->name('availability');
    Route::get('/{hotel}/rooms/{roomType}', [RoomController::class, 'show'])->name('room.show');
    Route::get('/{hotel}/rooms/{roomType}/calendar/{year}/{month}', [RoomController::class, 'calendar'])
        ->name('room.calendar')
        ->whereNumber(['year', 'month']);
});

// ── Authenticated customers ───────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Customer account
    Route::get('/dashboard', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/bookings', [AccountController::class, 'bookings'])->name('bookings');
        Route::get('/bookings/{booking}', [AccountController::class, 'showBooking'])->name('booking.show');
        Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
        Route::post('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
        Route::post('/password', [AccountController::class, 'changePassword'])->name('password.update');
    });

    // Reservation cart
    Route::prefix('booking/cart')->name('booking.cart.')->group(function () {
        Route::get('/', [BookingCartController::class, 'index'])->name('index');
        Route::post('/', [BookingCartController::class, 'store'])->name('store');
        Route::delete('/{item}', [BookingCartController::class, 'destroy'])->name('destroy');
        Route::post('/coupon', [BookingCartController::class, 'coupon'])->name('coupon');
        Route::post('/preview', [BookingCartController::class, 'preview'])->name('preview');
    });

    // Booking alias so views can use route('booking.cart')
    Route::get('/booking/cart', [BookingCartController::class, 'index'])->name('booking.cart');

    // Checkout & booking lifecycle
    Route::prefix('booking')->name('booking.')->group(function () {
        Route::get('/checkout', [BookingController::class, 'checkout'])->name('checkout');
        Route::post('/checkout', [BookingController::class, 'store'])->name('store');
        Route::get('/{bookingNumber}', [BookingController::class, 'show'])->name('show');
        Route::post('/{bookingNumber}/cancel', [BookingController::class, 'cancel'])->name('cancel');
        Route::get('/{bookingNumber}/invoice', [BookingController::class, 'invoice'])->name('invoice');
    });

    // Favourites
    Route::prefix('favorites')->name('favorites.')->group(function () {
        Route::get('/', [FavoriteController::class, 'index'])->name('index');
        Route::post('/{hotel}/toggle', [FavoriteController::class, 'toggle'])->name('toggle');
        Route::delete('/{hotel}', [FavoriteController::class, 'destroy'])->name('destroy');
    });

    // ── Admin ─────────────────────────────────────────────────────────────────
    Route::middleware('can:access-admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');

        // Hotels
        Route::prefix('hotels')->name('hotels.')->group(function () {
            Route::get('/', [AdminHotelController::class, 'index'])->name('index');
            Route::get('/{hotel}', [AdminHotelController::class, 'show'])->name('show');
            Route::post('/{hotel}/approve', [AdminHotelController::class, 'approve'])->name('approve');
            Route::post('/{hotel}/suspend', [AdminHotelController::class, 'suspend'])->name('suspend');
            Route::post('/{hotel}/featured', [AdminHotelController::class, 'toggleFeatured'])->name('featured');
            Route::delete('/{hotel}', [AdminHotelController::class, 'destroy'])->name('destroy');
        });

        // Bookings
        Route::prefix('bookings')->name('bookings.')->group(function () {
            Route::get('/', [AdminBookingController::class, 'index'])->name('index');
            Route::get('/{booking}', [AdminBookingController::class, 'show'])->name('show');
            Route::post('/{booking}/confirm', [AdminBookingController::class, 'confirm'])->name('confirm');
            Route::post('/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('cancel');
            Route::post('/{booking}/check-in', [AdminBookingController::class, 'checkIn'])->name('check-in');
            Route::post('/{booking}/check-out', [AdminBookingController::class, 'checkOut'])->name('check-out');
        });

        // Users
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
            Route::post('/{user}/roles', [AdminUserController::class, 'assignRole'])->name('role.assign');
            Route::delete('/{user}/roles/{role}', [AdminUserController::class, 'revokeRole'])->name('role.revoke');
            Route::post('/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('toggle-active');
        });

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::match(['POST', 'PUT'], '/settings', [SettingsController::class, 'update'])->name('settings.update');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
            Route::get('/occupancy', [ReportController::class, 'occupancy'])->name('occupancy');
        });

        // Coupons
        Route::prefix('coupons')->name('coupons.')->group(function () {
            Route::get('/', [AdminCouponController::class, 'index'])->name('index');
            Route::get('/create', [AdminCouponController::class, 'create'])->name('create');
            Route::post('/', [AdminCouponController::class, 'store'])->name('store');
            Route::post('/{coupon}/toggle', [AdminCouponController::class, 'toggle'])->name('toggle');
            Route::delete('/{coupon}', [AdminCouponController::class, 'destroy'])->name('destroy');
        });
    });

    // ── Hotel Owner ───────────────────────────────────────────────────────────
    Route::middleware('can:access-owner')->prefix('owner')->name('owner.')->group(function () {
        Route::get('/', [OwnerDashboard::class, 'index'])->name('dashboard');

        // Hotel management
        Route::prefix('hotels')->name('hotels.')->group(function () {
            Route::get('/', [OwnerHotelController::class, 'index'])->name('index');
            Route::get('/create', [OwnerHotelController::class, 'create'])->name('create');
            Route::post('/', [OwnerHotelController::class, 'store'])->name('store');
            Route::get('/{hotel}', [OwnerHotelController::class, 'show'])->name('show');
            Route::get('/{hotel}/edit', [OwnerHotelController::class, 'edit'])->name('edit');
            Route::put('/{hotel}', [OwnerHotelController::class, 'update'])->name('update');

            // Room types
            Route::get('/{hotel}/room-types/create', [OwnerHotelController::class, 'createRoomType'])->name('room-types.create');
            Route::post('/{hotel}/room-types', [OwnerHotelController::class, 'storeRoomType'])->name('room-types.store');

            // Physical rooms
            Route::post('/{hotel}/room-types/{roomType}/rooms', [OwnerHotelController::class, 'storeRoom'])->name('rooms.store');

            // Images
            Route::post('/{hotel}/images', [OwnerHotelController::class, 'storeImages'])->name('images.store');
            Route::post('/images/{image}/set-cover', [OwnerHotelController::class, 'setCoverImage'])->name('images.set-cover');
            Route::delete('/images/{image}', [OwnerHotelController::class, 'deleteImage'])->name('images.destroy');

            // Coupons
            Route::prefix('/{hotel}/coupons')->name('coupons.')->group(function () {
                Route::get('/', [OwnerCouponController::class, 'index'])->name('index');
                Route::get('/create', [OwnerCouponController::class, 'create'])->name('create');
                Route::post('/', [OwnerCouponController::class, 'store'])->name('store');
                Route::post('/{coupon}/toggle', [OwnerCouponController::class, 'toggle'])->name('toggle');
                Route::delete('/{coupon}', [OwnerCouponController::class, 'destroy'])->name('destroy');
            });

            // Staff management
            Route::prefix('/{hotel}/staff')->name('staff.')->group(function () {
                Route::get('/', [OwnerStaffController::class, 'index'])->name('index');
                Route::get('/invite', [OwnerStaffController::class, 'create'])->name('create');
                Route::post('/', [OwnerStaffController::class, 'store'])->name('store');
                Route::post('/{user}/toggle', [OwnerStaffController::class, 'toggleActive'])->name('toggle');
                Route::delete('/{user}', [OwnerStaffController::class, 'destroy'])->name('destroy');
            });
        });

        // Booking management per hotel
        Route::prefix('hotels/{hotel}/bookings')->name('hotels.bookings.')->group(function () {
            Route::get('/', [OwnerBookingController::class, 'index'])->name('index');
            Route::get('/{booking}', [OwnerBookingController::class, 'show'])->name('show');
            Route::post('/{booking}/confirm', [OwnerBookingController::class, 'confirm'])->name('confirm');
            Route::post('/{booking}/check-in', [OwnerBookingController::class, 'checkIn'])->name('check-in');
            Route::post('/{booking}/check-out', [OwnerBookingController::class, 'checkOut'])->name('check-out');
            Route::post('/{booking}/cancel', [OwnerBookingController::class, 'cancel'])->name('cancel');
        });
    });

    // ── Receptionist ──────────────────────────────────────────────────────────
    Route::middleware(['auth', 'receptionist'])->prefix('receptionist')->name('receptionist.')->group(function () {
        Route::get('/', [ReceptionistDashboard::class, 'index'])->name('dashboard');

        // Bookings
        Route::prefix('bookings')->name('bookings.')->group(function () {
            Route::get('/', [ReceptionistBookingController::class, 'index'])->name('index');
            Route::get('/create', [ReceptionistBookingController::class, 'create'])->name('create');
            Route::post('/', [ReceptionistBookingController::class, 'store'])->name('store');
            Route::get('/{booking}', [ReceptionistBookingController::class, 'show'])->name('show');
            Route::post('/{booking}/confirm', [ReceptionistBookingController::class, 'confirm'])->name('confirm');
            Route::post('/{booking}/check-in', [ReceptionistBookingController::class, 'checkIn'])->name('check-in');
            Route::post('/{booking}/check-out', [ReceptionistBookingController::class, 'checkOut'])->name('check-out');
            Route::post('/{booking}/cancel', [ReceptionistBookingController::class, 'cancel'])->name('cancel');
            Route::get('/{booking}/invoice', [ReceptionistBookingController::class, 'invoice'])->name('invoice');
        });

        // Availability
        Route::get('/availability', [ReceptionistAvailability::class, 'index'])->name('availability');

        // Guests
        Route::prefix('guests')->name('guests.')->group(function () {
            Route::get('/', [ReceptionistGuestController::class, 'index'])->name('index');
            Route::get('/{guest}', [ReceptionistGuestController::class, 'show'])->name('show');
        });
    });
});
