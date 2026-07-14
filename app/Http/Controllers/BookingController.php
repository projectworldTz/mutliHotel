<?php

namespace App\Http\Controllers;

use App\Enums\Feature;
use App\Events\BookingCreated;
use App\Http\Requests\StoreBookingRequest;
use App\Models\RoomType;
use App\Models\User;
use App\Repositories\RoomRepository;
use App\Services\BookingService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private PaymentService $paymentService,
        private InvoiceService $invoiceService,
        private RoomRepository $roomRepository,
    ) {}

    /**
     * Checkout page — validate cart is non-empty before showing.
     */
    public function checkout()
    {
        /** @var User $user */
        $user = Auth::user();
        $cart = $this->bookingService->getCart($user);

        if ($cart->items->isEmpty()) {
            return redirect()->route('hotels.index')
                ->with('warning', 'Your cart is empty. Please select a room first.');
        }

        $hotel = $cart->items->first()?->roomType?->hotel;

        $upsellingEnabled = (bool) $hotel?->hasFeature(Feature::UPSELLING);
        $mealPackages   = $upsellingEnabled ? $hotel->mealPackages()->active()->get() : collect();
        $upgradeOptions = $upsellingEnabled ? $this->buildUpgradeOptions($hotel, $cart) : [];

        // Owner has switched off online payment — show manual numbers instead of a gateway picker
        if ($hotel?->manual_payment_enabled) {
            $manualPayment = true;
            $manualNumbers = $hotel->manualPaymentNumbers();
            $gateways      = [];

            return view('booking.checkout', compact('cart', 'gateways', 'hotel', 'manualPayment', 'manualNumbers', 'mealPackages', 'upgradeOptions'));
        }

        // Resolve which payment methods the hotel owner has enabled
        $enabledKeys   = $hotel ? $hotel->enabledPaymentMethods() : \App\Models\Hotel::ALL_PAYMENT_METHODS;
        $allGateways   = $this->paymentService->availableGateways();
        $gateways      = array_filter($allGateways, fn($g) => in_array($g['key'], $enabledKeys));
        $manualPayment = false;
        $manualNumbers = [];

        return view('booking.checkout', compact('cart', 'gateways', 'hotel', 'manualPayment', 'manualNumbers', 'mealPackages', 'upgradeOptions'));
    }

    /**
     * For each cart item, find higher-tier room types (by base_price) in the
     * same hotel that have at least one room free for that item's exact stay
     * window — offered as a paid upgrade at checkout. Keyed by cart item id.
     */
    private function buildUpgradeOptions(\App\Models\Hotel $hotel, \App\Models\ReservationCart $cart): array
    {
        $options = [];
        $availabilityCache = [];

        foreach ($cart->items as $item) {
            $currentType = $item->roomType;
            if (! $currentType) {
                continue;
            }

            $dateKey = $item->check_in . '_' . $item->check_out;
            if (! isset($availabilityCache[$dateKey])) {
                $availabilityCache[$dateKey] = $this->roomRepository->availableCountPerType(
                    $hotel, $item->check_in, $item->check_out
                );
            }
            $availableCounts = $availabilityCache[$dateKey];

            $candidates = RoomType::where('hotel_id', $hotel->id)
                ->where('id', '!=', $currentType->id)
                ->where('base_price', '>', $currentType->base_price)
                ->active()
                ->orderBy('base_price')
                ->get()
                ->filter(fn ($rt) => ($availableCounts[$rt->id] ?? 0) > 0)
                ->map(fn ($rt) => [
                    'room_type_id'  => $rt->id,
                    'name'          => $rt->name,
                    'nightly_diff'  => (float) $rt->base_price - (float) $currentType->base_price,
                ])
                ->values();

            if ($candidates->isNotEmpty()) {
                $options[$item->id] = $candidates;
            }
        }

        return $options;
    }

    /**
     * Create the booking from cart contents, then hand off to payment.
     */
    public function store(StoreBookingRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $booking = $this->bookingService->createFromCart($user, $request->validated());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }

        if ($booking->hotel?->manual_payment_enabled) {
            \App\Models\Payment::create([
                'booking_id' => $booking->id,
                'method'     => 'manual',
                'status'     => 'pending',
                'amount'     => $booking->grand_total,
                'currency'   => $booking->currency ?? config('app.currency'),
                'metadata'   => ['note' => 'Awaiting manual payment confirmation by the hotel.'],
            ]);

            return redirect()
                ->route('booking.show', $booking->booking_number)
                ->with(['manual_payment' => true]);
        }

        $paymentMethod   = $request->validated()['payment_method'];
        $phoneNumber     = $request->validated()['phone_number'] ?? null;

        $paymentResponse = $this->paymentService->initiate($booking, $paymentMethod, [
            'phone_number' => $phoneNumber,
        ]);

        // Redirect-based gateways (e.g. card via DPO Pay) send the guest to a
        // hosted payment page instead of showing a phone/PIN pending screen.
        if (! empty($paymentResponse['redirect_url'])) {
            return redirect()->away($paymentResponse['redirect_url']);
        }

        return redirect()
            ->route('booking.show', $booking->booking_number)
            ->with([
                'payment_pending'  => true,
                'payment_phone'    => $phoneNumber,
                'payment_provider' => $paymentMethod,
                'payment_message'  => $paymentResponse['message'],
            ]);
    }

    /**
     * Booking confirmation / detail page.
     */
    public function show(string $bookingNumber)
    {
        /** @var User $user */
        $user    = Auth::user();
        $booking = $this->bookingService->findByNumber($bookingNumber);

        abort_unless($booking->user_id === Auth::id() || $user->isSuperAdmin(), 403);

        $booking->loadMissing([
            'hotel.images', 'rooms.roomType', 'payment', 'invoice', 'mealPackages',
            'maintenanceRequests', 'messages', 'digitalCheckin',
        ]);

        return view('booking.show', compact('booking'));
    }

    /**
     * Cancel a booking.
     */
    public function cancel(string $bookingNumber, Request $request)
    {
        $booking = $this->bookingService->findByNumber($bookingNumber);

        abort_unless($booking->user_id === Auth::id(), 403);

        try {
            $booking = $this->bookingService->cancel($booking, $request->input('reason', ''));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['cancel' => $e->getMessage()]);
        }

        $refund  = $booking->refund_amount;
        $message = $refund > 0
            ? "Booking #{$booking->booking_number} cancelled. Refund of {$booking->currency} " . number_format((float) $refund, 2) . " will be transferred within 2–3 business days."
            : "Booking #{$booking->booking_number} has been cancelled.";

        return redirect()->route('account.bookings')->with('success', $message);
    }

    /**
     * Development-only: simulate a successful mobile money payment confirmation.
     * This endpoint is disabled in production.
     */
    public function devConfirmPayment(\App\Models\Payment $payment)
    {
        abort_unless(app()->environment(['local', 'staging']), 404);

        $this->paymentService->verify($payment, [
            'transaction_id' => 'DEV-SIM-' . now()->format('YmdHis'),
        ]);

        $booking = $payment->booking;
        $this->bookingService->confirm($booking);
        $booking->refresh();
        event(new BookingCreated($booking));

        return redirect()
            ->route('booking.show', $booking->booking_number)
            ->with(['success' => 'Payment confirmed (development simulation).']);
    }

    /**
     * Development-only: stand-in for DPO Pay's hosted card checkout page.
     * Real DPO integration would redirect here instead once credentials are set;
     * this endpoint is disabled in production.
     */
    public function dpoSimulate(\App\Models\Payment $payment)
    {
        abort_unless(app()->environment(['local', 'staging']), 404);
        abort_if($payment->method !== 'dpo_card', 404);

        return view('booking.dpo-simulate', compact('payment'));
    }

    /**
     * Download booking invoice as PDF.
     */
    public function invoice(string $bookingNumber)
    {
        /** @var User $user */
        $user    = Auth::user();
        $booking = $this->bookingService->findByNumber($bookingNumber);

        abort_unless($booking->user_id === Auth::id() || $user->isSuperAdmin(), 403);

        $invoice = $booking->invoice;
        abort_if(is_null($invoice), 404, 'Invoice not found.');

        return $this->invoiceService->download($invoice);
    }
}
