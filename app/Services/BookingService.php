<?php

namespace App\Services;

use App\Enums\Feature;
use App\Models\Booking;
use App\Services\CancellationService;
use App\Models\BookingRoom;
use App\Models\Hotel;
use App\Models\HousekeepingTask;
use App\Models\Payment;
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
        private CancellationService    $cancellationService,
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
    public function addToCart(User $user, RoomType $roomType, string $checkIn, string $checkOut, int $guests, ?\App\Models\CorporateAccount $corporate = null): ReservationCartItem
    {
        // Reject if the hotel has disabled online booking
        if (! $roomType->hotel->online_booking_enabled) {
            throw new \RuntimeException('Online booking is currently unavailable for this hotel.');
        }

        // Validate dates
        $dateCheck = $this->availabilityService->validateDates($checkIn, $checkOut);
        if (! $dateCheck['valid']) {
            throw new \RuntimeException(implode(' ', $dateCheck['errors']));
        }

        // Find an available room
        $room = $this->roomRepository->firstAvailableRoom($roomType, $checkIn, $checkOut);

        if (! $room) {
            $nextFree = $this->availabilityRepository->nextAvailableDateForType($roomType, $checkIn);
            $hint = $nextFree
                ? ' Rooms are next available from ' . \Carbon\Carbon::parse($nextFree)->format('M j, Y') . '.'
                : '';
            throw new \RuntimeException("All {$roomType->name} rooms are fully booked for those dates.{$hint}");
        }

        // Calculate pricing
        $pricing = $this->pricingService->calculateForStay($roomType, $checkIn, $checkOut);

        // Apply corporate discount if provided
        if ($corporate) {
            $discountedRate = $corporate->applyDiscount((float) $pricing['nightly_rate']);
            $pricing['nightly_rate'] = $discountedRate;
            $pricing['subtotal']     = $discountedRate * $pricing['nights'];
        }

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
            // 1. Pessimistic-lock each room row, then verify availability.
            //    lockForUpdate() holds a row-level write lock for the duration
            //    of the transaction, so two concurrent checkouts for the same
            //    room cannot both pass the availability check.
            foreach ($cart->items as $item) {
                $room = Room::where('id', $item->room_id)->lockForUpdate()->firstOrFail();

                if (! $room->isAvailableForDates($item->check_in, $item->check_out)) {
                    throw new \RuntimeException(
                        "Sorry, {$room->room_number} is no longer available for your selected dates — " .
                        "someone else just booked it. Please choose different dates or another room."
                    );
                }
                $item->refreshPricing();
            }

            // 2. Calculate totals
            $subtotal  = (float) $cart->items->sum('sub_total');
            $hotel     = $cart->items->first()->room->hotel;
            $totals    = $this->pricingService->calculateOrderTotal($subtotal);

            // 4. Determine overall check-in/out (may span multiple room items)
            $checkIn  = $cart->items->min('check_in');
            $checkOut = $cart->items->max('check_out');
            $nights   = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));

            // 5. Resolve corporate account from session (set when adding from portal)
            $corporateAccountId = session('corporate_account_id');
            if ($corporateAccountId) {
                $validCorporate = \App\Models\CorporateAccount::where('id', $corporateAccountId)
                    ->where('hotel_id', $hotel->id)
                    ->where('is_active', true)
                    ->first();
                $corporateAccountId = ($validCorporate && $validCorporate->isContractActive())
                    ? $validCorporate->id
                    : null;
            }

            // 6. Create the Booking record
            $booking = Booking::create([
                'booking_number'               => Booking::generateBookingNumber(),
                'user_id'                      => $user->id,
                'hotel_id'                     => $hotel->id,
                'corporate_account_id'         => $corporateAccountId,
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
                'grand_total'                  => $totals['grand_total'],
                'currency'                     => config('app.currency'),
                'special_requests'             => $checkoutData['special_requests'] ?? null,
                'cancellation_policy_snapshot' => json_encode($this->cancellationService->policySnapshot()),
            ]);

            // 7. Create BookingRoom records and block dates
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

            // 7. Create the invoice
            $this->invoiceService->generateForBooking($booking);

            // 9. Clear cart and corporate session
            $cart->items()->delete();
            $cart->delete();
            session()->forget('corporate_account_id');

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

        // Confirming a booking (incl. a receptionist/owner/admin confirming a
        // manual payment) means payment has been received — reflect that on
        // the payment record and invoice, which otherwise stay stuck on
        // "pending"/"draft" since nothing else transitions them.
        $payment = Payment::where('booking_id', $booking->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($payment) {
            $payment->update(['status' => 'paid']);
        }

        if ($booking->invoice && $booking->invoice->status !== 'paid') {
            $this->invoiceService->markPaid($booking->invoice);
        }

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

        // Auto-create housekeeping tasks for each room if the hotel has the feature
        $booking->loadMissing(['hotel', 'rooms.room']);
        if ($booking->hotel->hasFeature(Feature::HOUSEKEEPING)) {
            foreach ($booking->rooms as $bookingRoom) {
                HousekeepingTask::create([
                    'hotel_id'   => $booking->hotel_id,
                    'room_id'    => $bookingRoom->room_id,
                    'booking_id' => $booking->id,
                    'type'       => HousekeepingTask::TYPE_CHECKOUT,
                    'status'     => HousekeepingTask::STATUS_PENDING,
                    'priority'   => HousekeepingTask::PRIORITY_HIGH,
                ]);
            }
        }

        return $booking;
    }

    /**
     * Cancel a booking, compute refund, and fire the BookingCancelled event.
     *
     * @throws \RuntimeException when booking is not cancellable
     */
    public function cancel(Booking $booking, string $reason = ''): Booking
    {
        return $this->cancellationService->cancel($booking, $reason);
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
