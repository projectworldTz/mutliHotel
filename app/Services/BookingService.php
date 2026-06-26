<?php

namespace App\Services;

use App\Events\BookingCancelled;
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\Coupon;
use App\Models\Hotel;
use App\Models\ReservationCart;
use App\Models\ReservationCartItem;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Setting;
use App\Models\User;
use App\Repositories\AvailabilityRepository;
use App\Repositories\BookingRepository;
use App\Repositories\RoomRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        private BookingRepository      $repository,
        private RoomRepository         $roomRepository,
        private AvailabilityRepository $availabilityRepository,
        private AvailabilityService    $availabilityService,
        private PricingService         $pricingService,
        private InvoiceService         $invoiceService,
    ) {}

    // ── Cart management ───────────────────────────────────────────────────────

    public function getOrCreateCart(User $user): ReservationCart
    {
        return ReservationCart::firstOrCreate(
            ['user_id' => $user->id],
            ['expires_at' => now()->addHours(2)]
        );
    }

    public function getCart(User $user): ReservationCart
    {
        return ReservationCart::where('user_id', $user->id)
            ->with(['items.room.hotel', 'items.roomType.images'])
            ->firstOrCreate(['user_id' => $user->id], ['expires_at' => now()->addHours(2)]);
    }

    /**
     * Add a room to the reservation cart.
     * Re-prices against current seasonal rates and validates availability.
     *
     * @throws \RuntimeException when room is unavailable
     */
    public function addToCart(User $user, RoomType $roomType, string $checkIn, string $checkOut, int $guests): ReservationCartItem
    {
        // Validate dates
        $dateCheck = $this->availabilityService->validateDates($checkIn, $checkOut);
        if (! $dateCheck['valid']) {
            throw new \RuntimeException(implode(' ', $dateCheck['errors']));
        }

        // Find an available room
        $room = $this->roomRepository->firstAvailableRoom($roomType, $checkIn, $checkOut);

        if (! $room) {
            throw new \RuntimeException('No rooms are available for the selected dates.');
        }

        // Calculate pricing
        $pricing = $this->pricingService->calculateForStay($roomType, $checkIn, $checkOut);

        // Create/refresh cart
        $cart = $this->getOrCreateCart($user);
        $cart->update(['expires_at' => now()->addHours(2)]);

        // Remove any existing item for the same room type + overlapping dates
        $cart->items()
            ->where('room_type_id', $roomType->id)
            ->where('check_in', $checkIn)
            ->where('check_out', $checkOut)
            ->delete();

        return $cart->items()->create([
            'room_id'      => $room->id,
            'room_type_id' => $roomType->id,
            'check_in'     => $checkIn,
            'check_out'    => $checkOut,
            'guests'       => $guests,
            'nightly_rate' => $pricing['nightly_rate'],
            'nights'       => $pricing['nights'],
            'sub_total'    => $pricing['subtotal'],
        ]);
    }

    public function removeFromCart(ReservationCartItem $item): void
    {
        $item->delete();
    }

    public function clearCart(User $user): void
    {
        ReservationCart::where('user_id', $user->id)->with('items')->get()
            ->each(function ($cart) {
                $cart->items()->delete();
                $cart->delete();
            });
    }

    /**
     * Preview coupon discount against the current cart subtotal.
     * Returns ['valid' => bool, 'discount' => float, 'message' => string].
     */
    public function applyCouponPreview(User $user, string $code, ?int $hotelId = null): array
    {
        $cart   = $this->getCart($user);
        $coupon = Coupon::where('code', strtoupper($code))->valid()->first();

        if (! $coupon) {
            return ['valid' => false, 'discount' => 0.0, 'message' => 'Coupon code is invalid or expired.'];
        }

        // Hotel-scoped coupon — must match
        if ($coupon->hotel_id && $coupon->hotel_id !== $hotelId) {
            return ['valid' => false, 'discount' => 0.0, 'message' => 'This coupon is not valid for this hotel.'];
        }

        $discount = $coupon->calculateDiscount($cart->sub_total);

        if ($discount <= 0) {
            return ['valid' => false, 'discount' => 0.0, 'message' => "Minimum booking amount for this coupon is \${$coupon->min_booking_amount}."];
        }

        return ['valid' => true, 'discount' => $discount, 'message' => "Coupon applied. You save \${$discount}."];
    }

    // ── Checkout & booking creation ───────────────────────────────────────────

    /**
     * Convert the cart into a confirmed Booking inside a DB transaction.
     * Blocks all room dates atomically. Returns the created Booking.
     *
     * @throws \RuntimeException on availability conflict or empty cart
     */
    public function createFromCart(User $user, array $checkoutData): Booking
    {
        $cart = $this->getCart($user);

        if ($cart->items->isEmpty()) {
            throw new \RuntimeException('Your cart is empty.');
        }

        return DB::transaction(function () use ($user, $cart, $checkoutData) {
            // 1. Refresh pricing and confirm rooms are still available
            foreach ($cart->items as $item) {
                if (! $item->room->isAvailableForDates($item->check_in, $item->check_out)) {
                    throw new \RuntimeException(
                        "Room {$item->room->room_number} is no longer available for your selected dates. Please choose different dates."
                    );
                }
                $item->refreshPricing();
            }

            // 2. Resolve coupon
            $coupon = null;
            if (! empty($checkoutData['coupon_code'])) {
                $coupon = Coupon::where('code', strtoupper($checkoutData['coupon_code']))->valid()->first();
            }

            // 3. Calculate totals
            $subtotal  = (float) $cart->items->sum('sub_total');
            $hotel     = $cart->items->first()->room->hotel;
            $totals    = $this->pricingService->calculateOrderTotal($subtotal, $coupon);

            // 4. Determine overall check-in/out (may span multiple room items)
            $checkIn  = $cart->items->min('check_in');
            $checkOut = $cart->items->max('check_out');
            $nights   = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));

            // 5. Create the Booking record
            $booking = Booking::create([
                'booking_number'               => Booking::generateBookingNumber(),
                'user_id'                      => $user->id,
                'hotel_id'                     => $hotel->id,
                'coupon_id'                    => $coupon?->id,
                'status'                       => Booking::STATUS_PENDING,
                'check_in'                     => $checkIn,
                'check_out'                    => $checkOut,
                'nights'                       => $nights,
                'guests_adults'                => $checkoutData['guests_adults'] ?? 1,
                'guests_children'              => $checkoutData['guests_children'] ?? 0,
                'sub_total'                    => $totals['subtotal'],
                'tax_total'                    => $totals['tax_total'],
                'tax_rate'                     => $totals['tax_rate'],
                'discount_total'               => $totals['discount_total'],
                'coupon_code'                  => $totals['coupon_code'],
                'grand_total'                  => $totals['grand_total'],
                'currency'                     => 'USD',
                'special_requests'             => $checkoutData['special_requests'] ?? null,
                'cancellation_policy_snapshot' => $hotel->cancellation_policy,
            ]);

            // 6. Create BookingRoom records and block dates
            foreach ($cart->items as $item) {
                BookingRoom::create([
                    'booking_id'   => $booking->id,
                    'room_id'      => $item->room_id,
                    'room_type_id' => $item->room_type_id,
                    'check_in'     => $item->check_in,
                    'check_out'    => $item->check_out,
                    'nightly_rate' => $item->nightly_rate,
                    'nights'       => $item->nights,
                    'sub_total'    => $item->sub_total,
                ]);

                $this->availabilityRepository->blockForBooking($item->room, $booking);
            }

            // 7. Increment coupon usage
            $coupon?->incrementUses();

            // 8. Create the invoice
            $this->invoiceService->generateForBooking($booking);

            // 9. Clear cart
            $cart->items()->delete();
            $cart->delete();

            return $booking->fresh(['rooms.room', 'rooms.roomType', 'hotel', 'invoice']);
        });
    }

    // ── Status transitions ────────────────────────────────────────────────────

    public function confirm(Booking $booking): Booking
    {
        $booking->update([
            'status'       => Booking::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);

        return $booking;
    }

    public function checkIn(Booking $booking): Booking
    {
        $booking->update([
            'status'        => Booking::STATUS_CHECKED_IN,
            'checked_in_at' => now(),
        ]);

        return $booking;
    }

    public function checkOut(Booking $booking): Booking
    {
        $booking->update([
            'status'         => Booking::STATUS_CHECKED_OUT,
            'checked_out_at' => now(),
        ]);

        return $booking;
    }

    /**
     * Cancel a booking and release all blocked room dates.
     *
     * @throws \RuntimeException when booking is not cancellable
     */
    public function cancel(Booking $booking, string $reason = ''): Booking
    {
        if (! $booking->is_cancellable) {
            throw new \RuntimeException("Booking #{$booking->booking_number} cannot be cancelled in its current status.");
        }

        DB::transaction(function () use ($booking, $reason) {
            $booking->update([
                'status'              => Booking::STATUS_CANCELLED,
                'cancellation_reason' => $reason,
                'cancelled_at'        => now(),
            ]);

            $this->availabilityRepository->releaseAllForBooking($booking);
        });

        $booking->refresh();
        event(new BookingCancelled($booking));

        return $booking;
    }

    // ── Retrieval delegates ───────────────────────────────────────────────────

    public function getUserBookings(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->forUser($user, $perPage);
    }

    public function getHotelBookings(Hotel $hotel, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->forHotel($hotel, $filters, $perPage);
    }

    public function find(int $id): Booking
    {
        $booking = $this->repository->find($id);

        abort_if(is_null($booking), 404, 'Booking not found.');

        return $booking;
    }

    public function findByNumber(string $number): Booking
    {
        $booking = $this->repository->findByNumber($number);

        abort_if(is_null($booking), 404, 'Booking not found.');

        return $booking;
    }

    public function allPaginated(array $filters = [], int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->repository->allPaginated($filters, $perPage);
    }

    public function platformStats(): array
    {
        return $this->repository->platformStats();
    }

    public function hotelStats(Hotel $hotel): array
    {
        return $this->repository->hotelStats($hotel);
    }

    public function revenueByMonth(int $months = 12): array
    {
        return $this->repository->revenueByMonth($months);
    }
}
