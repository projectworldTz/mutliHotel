<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ErrorLogController;
use App\Http\Controllers\Admin\FeatureRequestController as AdminFeatureRequestController;
use App\Http\Controllers\Admin\HotelFeatureController;
use App\Http\Controllers\Owner\FeatureRequestController as OwnerFeatureRequestController;
use App\Http\Controllers\Owner\CorporateAccountController as OwnerCorporateController;
use App\Http\Controllers\CorporatePortalController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\HotelController as AdminHotelController;
use App\Http\Controllers\Admin\HotelDashboardController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingCartController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
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
use App\Http\Controllers\Owner\AnalyticsController as OwnerAnalyticsController;
use App\Http\Controllers\Owner\HousekeepingController as OwnerHousekeepingController;
use App\Http\Controllers\Owner\InventoryController as OwnerInventoryController;
use App\Http\Controllers\Receptionist\GuestController as ReceptionistGuestController;
use App\Http\Controllers\Receptionist\HousekeepingController as ReceptionistHousekeepingController;
use App\Http\Controllers\Receptionist\InventoryController as ReceptionistInventoryController;
use App\Http\Controllers\Receptionist\CancellationApprovalController as ReceptionistCancellationController;
use App\Http\Controllers\Owner\CancellationApprovalController as OwnerCancellationController;
use App\Http\Controllers\Owner\MealPackageController as OwnerMealPackageController;
use App\Http\Controllers\Owner\CampaignController as OwnerCampaignController;
use App\Http\Controllers\Owner\SurveyController as OwnerSurveyController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\Accountant\DashboardController as AccountantDashboard;
use App\Http\Controllers\Accountant\InvoiceController as AccountantInvoiceController;
use App\Http\Controllers\Accountant\ReportController as AccountantReportController;
use App\Http\Controllers\Accountant\ExpenseController as AccountantExpenseController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\Owner\MaintenanceController as OwnerMaintenanceController;
use App\Http\Controllers\Owner\ShiftController as OwnerShiftController;
use App\Http\Controllers\Owner\GroupBookingController as OwnerGroupBookingController;
use App\Http\Controllers\Receptionist\MaintenanceController as ReceptionistMaintenanceController;
use App\Http\Controllers\Receptionist\MessageController as ReceptionistMessageController;
use App\Http\Controllers\Receptionist\CheckinController as ReceptionistCheckinController;
use App\Http\Controllers\Receptionist\ShiftController as ReceptionistShiftController;
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

// ── AI Concierge Chat ─────────────────────────────────────────────────────────
Route::prefix('chat')->name('chat.')->middleware('throttle:30,1')->group(function () {
    Route::post('/message', [\App\Http\Controllers\ChatController::class, 'message'])->name('message');
    Route::post('/clear',   [\App\Http\Controllers\ChatController::class, 'clear'])->name('clear');
});

// ── Public ───────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

// Blog (unchanged domain, keep existing routes)
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post}', [BlogController::class, 'show'])->name('blog.show');

// Hotel browsing — marketplace directory across all active hotels
Route::prefix('hotels')->name('hotels.')->group(function () {
    Route::get('/', [HotelController::class, 'index'])->name('index');
    Route::get('/{hotel}', [HotelController::class, 'show'])->name('show');
    Route::get('/{hotel}/availability', [HotelController::class, 'availability'])->name('availability');
    Route::get('/{hotel}/rooms/{roomType}', [RoomController::class, 'show'])->name('room.show');
    Route::get('/{hotel}/rooms/{roomType}/calendar/{year}/{month}', [RoomController::class, 'calendar'])
        ->name('room.calendar')
        ->whereNumber(['year', 'month']);
});

// ── Corporate / B2B Portal (public — access via unique code) ─────────────────
Route::get('/corporate/{hotel:slug}/{code}', [CorporatePortalController::class, 'show'])
    ->name('corporate.portal');

// ── Guest Satisfaction Survey (public — access via emailed token link) ───────
Route::get('/survey/{token}', [SurveyController::class, 'show'])->name('survey.show');
Route::post('/survey/{token}', [SurveyController::class, 'store'])->name('survey.store');

// ── Authenticated customers ───────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Role-based dashboard redirect — each role lands on its own area
    Route::get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard');
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
        Route::post('/{bookingNumber}/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
        Route::post('/{bookingNumber}/messages', [MessageController::class, 'store'])->name('messages.store');
        Route::post('/{bookingNumber}/checkin', [CheckinController::class, 'store'])->name('checkin.store');
    });

    // Development-only: simulate mobile money payment confirmation
    if (app()->environment(['local', 'staging'])) {
        Route::post('/dev/payments/{payment}/confirm', [BookingController::class, 'devConfirmPayment'])
            ->name('dev.payment.confirm');

        // Stand-in for DPO Pay's hosted card checkout page until real credentials are configured.
        Route::get('/dev/payments/{payment}/dpo-simulate', [BookingController::class, 'dpoSimulate'])
            ->name('dpo.simulate');
    }

    // Favourites
    Route::prefix('favorites')->name('favorites.')->group(function () {
        Route::get('/', [FavoriteController::class, 'index'])->name('index');
        Route::post('/{hotel}/toggle', [FavoriteController::class, 'toggle'])->name('toggle');
        Route::delete('/{hotel}', [FavoriteController::class, 'destroy'])->name('destroy');
    });

    // ── Admin ─────────────────────────────────────────────────────────────────
    Route::middleware('can:access-admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');

        // Hotels — list + moderation actions
        Route::prefix('hotels')->name('hotels.')->group(function () {
            Route::get('/',                [AdminHotelController::class,   'index'])->name('index');
            Route::get('/create',          [AdminHotelController::class,   'create'])->name('create');
            Route::post('/',               [AdminHotelController::class,   'store'])->name('store');
            Route::post('/{hotel}/approve', [AdminHotelController::class, 'approve'])->name('approve');
            Route::post('/{hotel}/suspend', [AdminHotelController::class, 'suspend'])->name('suspend');
            Route::post('/{hotel}/featured', [AdminHotelController::class, 'toggleFeatured'])->name('featured');
            Route::delete('/{hotel}',       [AdminHotelController::class, 'destroy'])->name('destroy');

            // Hotel management hub — overview + drill-down tabs
            Route::get('/{hotel}',          [HotelDashboardController::class, 'show'])->name('show');
            Route::prefix('{hotel}/hub')->name('hub.')->group(function () {
                Route::get('/bookings', [HotelDashboardController::class, 'bookings'])->name('bookings');
                Route::get('/revenue',  [HotelDashboardController::class, 'revenue'])->name('revenue');
                Route::get('/rooms',    [HotelDashboardController::class, 'rooms'])->name('rooms');
                Route::get('/staff',    [HotelDashboardController::class, 'staff'])->name('staff');
                Route::get('/guests',   [HotelDashboardController::class, 'guests'])->name('guests');
                Route::get('/features', [HotelDashboardController::class, 'features'])->name('features');
            });
            // Feature grant/revoke
            Route::prefix('{hotel}/features')->name('features.')->group(function () {
                Route::post('/',   [HotelFeatureController::class, 'grant'])->name('grant');
                Route::delete('/', [HotelFeatureController::class, 'revoke'])->name('revoke');
            });
        });

        // Impersonation
        Route::post('/impersonate/{user}', [ImpersonationController::class, 'start'])->name('impersonate.start');
        Route::delete('/impersonate',      [ImpersonationController::class, 'stop'])->name('impersonate.stop');

        // Audit logs
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        // Error logs
        Route::prefix('error-logs')->name('error-logs.')->group(function () {
            Route::get('/', [ErrorLogController::class, 'index'])->name('index');
            Route::get('/{errorLog}', [ErrorLogController::class, 'show'])->name('show');
            Route::patch('/{errorLog}', [ErrorLogController::class, 'update'])->name('update');
            Route::delete('/{errorLog}', [ErrorLogController::class, 'destroy'])->name('destroy');
        });

        // Feature access requests
        Route::prefix('feature-requests')->name('feature-requests.')->group(function () {
            Route::get('/', [AdminFeatureRequestController::class, 'index'])->name('index');
            Route::post('/{featureRequest}/approve', [AdminFeatureRequestController::class, 'approve'])->name('approve');
            Route::post('/{featureRequest}/deny',    [AdminFeatureRequestController::class, 'deny'])->name('deny');
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
            Route::get('/create', [AdminUserController::class, 'create'])->name('create');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
            Route::post('/{user}/roles', [AdminUserController::class, 'assignRole'])->name('assign-role');
            Route::delete('/{user}/roles/{role}', [AdminUserController::class, 'revokeRole'])->name('revoke-role');
            Route::patch('/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('toggle-active');
            Route::patch('/{user}/hotel-limit', [AdminUserController::class, 'updateHotelLimit'])->name('hotel-limit');
        });

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::match(['POST', 'PUT'], '/settings', [SettingsController::class, 'update'])->name('settings.update');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
            Route::get('/occupancy', [ReportController::class, 'occupancy'])->name('occupancy');
        });

    });

    // ── Hotel Owner ───────────────────────────────────────────────────────────
    Route::middleware(['can:access-owner', 'hotel.setup'])->prefix('owner')->name('owner.')->group(function () {
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
            Route::get('/{hotel}/room-types/{roomType}/edit', [OwnerHotelController::class, 'editRoomType'])->name('room-types.edit');
            Route::put('/{hotel}/room-types/{roomType}', [OwnerHotelController::class, 'updateRoomType'])->name('room-types.update');
            Route::delete('/{hotel}/room-types/{roomType}', [OwnerHotelController::class, 'destroyRoomType'])->name('room-types.destroy');
            Route::post('/{hotel}/room-types/{roomType}/images', [OwnerHotelController::class, 'storeRoomTypeImages'])->name('room-types.images.store');
            Route::post('/room-type-images/{image}/set-cover', [OwnerHotelController::class, 'setCoverRoomTypeImage'])->name('room-type-images.set-cover');
            Route::delete('/room-type-images/{image}', [OwnerHotelController::class, 'deleteRoomTypeImage'])->name('room-type-images.destroy');
            Route::delete('/room-type-images', [OwnerHotelController::class, 'deleteRoomTypeImages'])->name('room-type-images.bulk-destroy');

            // Physical rooms
            Route::post('/{hotel}/room-types/{roomType}/rooms', [OwnerHotelController::class, 'storeRoom'])->name('rooms.store');
            Route::put('/{hotel}/room-types/{roomType}/rooms/{room}', [OwnerHotelController::class, 'updateRoom'])->name('rooms.update');
            Route::delete('/{hotel}/room-types/{roomType}/rooms/{room}', [OwnerHotelController::class, 'destroyRoom'])->name('rooms.destroy');

            // Images
            Route::post('/{hotel}/images', [OwnerHotelController::class, 'storeImages'])->name('images.store');
            Route::post('/images/{image}/set-cover', [OwnerHotelController::class, 'setCoverImage'])->name('images.set-cover');
            Route::delete('/images/{image}', [OwnerHotelController::class, 'deleteImage'])->name('images.destroy');
            Route::delete('/images', [OwnerHotelController::class, 'deleteImages'])->name('images.bulk-destroy');

            // Promo videos
            Route::post('/{hotel}/videos', [OwnerHotelController::class, 'storeVideo'])->name('videos.store');
            Route::delete('/videos/{video}', [OwnerHotelController::class, 'deleteVideo'])->name('videos.destroy');

            // Payment methods
            Route::post('/{hotel}/payment-methods', [OwnerHotelController::class, 'updatePaymentMethods'])->name('payment-methods.update');

            // Manual payment fallback
            Route::post('/{hotel}/manual-payment', [OwnerHotelController::class, 'updateManualPayment'])->name('manual-payment.update');

            // Online booking toggle
            Route::post('/{hotel}/toggle-online-booking', [OwnerHotelController::class, 'toggleOnlineBooking'])->name('toggle-online-booking');

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

        // Housekeeping overview (read-only)
        Route::get('/hotels/{hotel}/housekeeping', [OwnerHousekeepingController::class, 'index'])->name('housekeeping.index');

        // Advanced Analytics
        Route::get('/hotels/{hotel}/analytics', [OwnerAnalyticsController::class, 'index'])->name('analytics.index');

        // Emergency Cancellation Approvals
        Route::prefix('hotels/{hotel}/cancellation-approvals')->name('cancellation-approvals.')->group(function () {
            Route::get('/',                       [OwnerCancellationController::class, 'index'])->name('index');
            Route::post('/{approval}/approve',    [OwnerCancellationController::class, 'approve'])->name('approve');
            Route::post('/{approval}/deny',       [OwnerCancellationController::class, 'deny'])->name('deny');
        });

        // Inventory & Assets
        Route::prefix('hotels/{hotel}/inventory')->name('inventory.')->group(function () {
            Route::get('/',           [OwnerInventoryController::class, 'index'])->name('index');
            Route::post('/',          [OwnerInventoryController::class, 'store'])->name('store');
            Route::put('/{asset}',    [OwnerInventoryController::class, 'update'])->name('update');
            Route::delete('/{asset}', [OwnerInventoryController::class, 'destroy'])->name('destroy');

            Route::post('/categories',            [OwnerInventoryController::class, 'storeCategory'])->name('categories.store');
            Route::delete('/categories/{category}', [OwnerInventoryController::class, 'destroyCategory'])->name('categories.destroy');
        });

        // Meal Packages
        Route::prefix('hotels/{hotel}/meal-packages')->name('meal-packages.')->group(function () {
            Route::get('/',                  [OwnerMealPackageController::class, 'index'])->name('index');
            Route::post('/',                 [OwnerMealPackageController::class, 'store'])->name('store');
            Route::put('/{mealPackage}',      [OwnerMealPackageController::class, 'update'])->name('update');
            Route::delete('/{mealPackage}',   [OwnerMealPackageController::class, 'destroy'])->name('destroy');
        });

        // Email Marketing Campaigns
        Route::prefix('hotels/{hotel}/campaigns')->name('campaigns.')->group(function () {
            Route::get('/',              [OwnerCampaignController::class, 'index'])->name('index');
            Route::get('/create',        [OwnerCampaignController::class, 'create'])->name('create');
            Route::post('/',             [OwnerCampaignController::class, 'store'])->name('store');
            Route::get('/{campaign}',    [OwnerCampaignController::class, 'show'])->name('show');
            Route::post('/{campaign}/send', [OwnerCampaignController::class, 'send'])->name('send');
            Route::delete('/{campaign}', [OwnerCampaignController::class, 'destroy'])->name('destroy');
        });

        // Guest Satisfaction Surveys
        Route::prefix('hotels/{hotel}/surveys')->name('surveys.')->group(function () {
            Route::get('/', [OwnerSurveyController::class, 'index'])->name('index');
        });

        // Maintenance Requests (read-only overview)
        Route::get('/hotels/{hotel}/maintenance', [OwnerMaintenanceController::class, 'index'])->name('maintenance.index');

        // Staff Scheduling
        Route::prefix('hotels/{hotel}/shifts')->name('shifts.')->group(function () {
            Route::get('/',            [OwnerShiftController::class, 'index'])->name('index');
            Route::post('/',           [OwnerShiftController::class, 'store'])->name('store');
            Route::delete('/{shift}',  [OwnerShiftController::class, 'destroy'])->name('destroy');
        });

        // Group Booking Manager
        Route::prefix('hotels/{hotel}/group-bookings')->name('group-bookings.')->group(function () {
            Route::get('/',                  [OwnerGroupBookingController::class, 'index'])->name('index');
            Route::post('/',                 [OwnerGroupBookingController::class, 'store'])->name('store');
            Route::put('/{groupBooking}',    [OwnerGroupBookingController::class, 'update'])->name('update');
            Route::delete('/{groupBooking}', [OwnerGroupBookingController::class, 'destroy'])->name('destroy');
        });

        // Premium Feature Requests
        Route::prefix('hotels/{hotel}/features')->name('hotels.features.')->group(function () {
            Route::get('/',  [OwnerFeatureRequestController::class, 'index'])->name('index');
            Route::post('/', [OwnerFeatureRequestController::class, 'store'])->name('request');
        });

        // Corporate / B2B Portal
        Route::prefix('hotels/{hotel}/corporate')->name('hotels.corporate.')->group(function () {
            Route::get('/',                          [OwnerCorporateController::class, 'index'])->name('index');
            Route::get('/create',                    [OwnerCorporateController::class, 'create'])->name('create');
            Route::post('/',                         [OwnerCorporateController::class, 'store'])->name('store');
            Route::get('/{corporate}',               [OwnerCorporateController::class, 'show'])->name('show');
            Route::get('/{corporate}/edit',          [OwnerCorporateController::class, 'edit'])->name('edit');
            Route::put('/{corporate}',               [OwnerCorporateController::class, 'update'])->name('update');
            Route::patch('/{corporate}/regenerate',  [OwnerCorporateController::class, 'regenerateCode'])->name('regenerate');
            Route::delete('/{corporate}',            [OwnerCorporateController::class, 'destroy'])->name('destroy');
        });
    });

    // ── Hotel Staff (receptionist / manager / cashier) ───────────────────────
    Route::middleware(['auth', 'hotel.staff'])->prefix('receptionist')->name('receptionist.')->group(function () {
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
        Route::get('/availability/{roomType}/calendar/{year}/{month}', [ReceptionistAvailability::class, 'calendar'])
            ->name('availability.calendar');

        // Guests
        Route::prefix('guests')->name('guests.')->group(function () {
            Route::get('/', [ReceptionistGuestController::class, 'index'])->name('index');
            Route::get('/{guest}', [ReceptionistGuestController::class, 'show'])->name('show');
        });

        // Housekeeping
        Route::prefix('housekeeping')->name('housekeeping.')->group(function () {
            Route::get('/',                              [ReceptionistHousekeepingController::class, 'index'])->name('index');
            Route::post('/',                             [ReceptionistHousekeepingController::class, 'store'])->name('store');
            Route::patch('/{task}/status',               [ReceptionistHousekeepingController::class, 'updateStatus'])->name('status');
            Route::post('/{task}/assign',                [ReceptionistHousekeepingController::class, 'assign'])->name('assign');
            Route::delete('/{task}',                     [ReceptionistHousekeepingController::class, 'destroy'])->name('destroy');
        });

        // Inventory
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/',          [ReceptionistInventoryController::class, 'index'])->name('index');
            Route::post('/',         [ReceptionistInventoryController::class, 'store'])->name('store');
            Route::put('/{asset}',   [ReceptionistInventoryController::class, 'update'])->name('update');

            Route::post('/categories',              [ReceptionistInventoryController::class, 'storeCategory'])->name('categories.store');
            Route::delete('/categories/{category}', [ReceptionistInventoryController::class, 'destroyCategory'])->name('categories.destroy');
        });

        // Emergency Cancellation Approvals
        Route::prefix('cancellation-approvals')->name('cancellation-approvals.')->group(function () {
            Route::get('/',                                      [ReceptionistCancellationController::class, 'index'])->name('index');
            Route::post('/bookings/{booking}/request',           [ReceptionistCancellationController::class, 'request'])->name('request');
            Route::post('/{approval}/execute',                   [ReceptionistCancellationController::class, 'execute'])->name('execute');
        });

        // Maintenance Requests
        Route::prefix('maintenance')->name('maintenance.')->group(function () {
            Route::get('/',              [ReceptionistMaintenanceController::class, 'index'])->name('index');
            Route::post('/',             [ReceptionistMaintenanceController::class, 'store'])->name('store');
            Route::patch('/{maintenanceRequest}/status', [ReceptionistMaintenanceController::class, 'updateStatus'])->name('status');
            Route::delete('/{maintenanceRequest}', [ReceptionistMaintenanceController::class, 'destroy'])->name('destroy');
        });

        // Guest Messaging
        Route::prefix('messages')->name('messages.')->group(function () {
            Route::get('/',              [ReceptionistMessageController::class, 'index'])->name('index');
            Route::get('/{booking}',     [ReceptionistMessageController::class, 'show'])->name('show');
            Route::post('/{booking}',    [ReceptionistMessageController::class, 'store'])->name('store');
        });

        // Digital Check-ins
        Route::prefix('checkins')->name('checkins.')->group(function () {
            Route::get('/',                            [ReceptionistCheckinController::class, 'index'])->name('index');
            Route::post('/{checkin}/verify',           [ReceptionistCheckinController::class, 'verify'])->name('verify');
        });

        // My Shifts
        Route::get('/shifts', [ReceptionistShiftController::class, 'index'])->name('shifts.index');
    });

    // ── Hotel Staff (accountant) ──────────────────────────────────────────────
    Route::middleware(['auth', 'hotel.staff:accountant'])->prefix('accountant')->name('accountant.')->group(function () {
        Route::get('/', [AccountantDashboard::class, 'index'])->name('dashboard');

        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [AccountantInvoiceController::class, 'index'])->name('index');
            Route::get('/{invoice}', [AccountantInvoiceController::class, 'show'])->name('show');
            Route::post('/{invoice}/mark-paid', [AccountantInvoiceController::class, 'markPaid'])->name('mark-paid');
            Route::post('/{invoice}/refund', [AccountantInvoiceController::class, 'refund'])->name('refund');
        });

        Route::get('/reports', [AccountantReportController::class, 'index'])->name('reports.index');

        Route::prefix('expenses')->name('expenses.')->group(function () {
            Route::get('/', [AccountantExpenseController::class, 'index'])->name('index');
            Route::post('/', [AccountantExpenseController::class, 'store'])->name('store');
            Route::put('/{expense}', [AccountantExpenseController::class, 'update'])->name('update');
            Route::delete('/{expense}', [AccountantExpenseController::class, 'destroy'])->name('destroy');
        });
    });
});

