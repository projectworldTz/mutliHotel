<?php

namespace App\Http\Controllers;

use App\Events\BookingCreated;
use App\Http\Requests\StoreBookingRequest;
use App\Services\BookingService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private PaymentService $paymentService,
        private InvoiceService $invoiceService,
    ) {}

    /**
     * Checkout page — validate cart is non-empty before showing.
     */
    public function checkout()
    {
        $cart = $this->bookingService->getCart(auth()->user());

        if ($cart->items->isEmpty()) {
            return redirect()->route('hotels.index')
                ->with('warning', 'Your cart is empty. Please select a room first.');
        }

        $gateways = $this->paymentService->availableGateways();

        return view('booking.checkout', compact('cart', 'gateways'));
    }

    /**
     * Create the booking from cart contents, then hand off to payment.
     */
    public function store(StoreBookingRequest $request)
    {
        try {
            $booking = $this->bookingService->createFromCart(auth()->user(), $request->validated());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }

        $paymentMethod   = $request->validated()['payment_method'];
        $paymentResponse = $this->paymentService->initiate(
            $booking,
            $paymentMethod,
            $request->only(['stripe_token', 'paypal_order_id'])
        );

        // Cash / bank transfer: confirm immediately, then email
        if (\in_array($paymentMethod, ['cash', 'bank'])) {
            $this->bookingService->confirm($booking);
            $booking->refresh();
            event(new BookingCreated($booking));

            $flash = ['booking_success' => true, 'success' => $paymentResponse['message']];

            // Bank transfer: include bank account details for the user to action
            if ($paymentMethod === 'bank' && ! empty($paymentResponse['bank_details'])) {
                $flash['bank_details'] = $paymentResponse['bank_details'];
            }

            return redirect()
                ->route('booking.show', $booking->booking_number)
                ->with($flash);
        }

        // Stripe / PayPal with a redirect URL (real gateway integration)
        if (! empty($paymentResponse['redirect_url'])) {
            return redirect($paymentResponse['redirect_url']);
        }

        // Stub path: gateway accepted without redirect — confirm immediately
        if ($paymentResponse['success']) {
            $this->bookingService->confirm($booking);
            $booking->refresh();
            event(new BookingCreated($booking));
        }

        return redirect()
            ->route('booking.show', $booking->booking_number)
            ->with(['booking_success' => true, 'success' => 'Booking placed successfully!']);
    }

    /**
     * Booking confirmation / detail page.
     */
    public function show(string $bookingNumber)
    {
        $booking = $this->bookingService->findByNumber($bookingNumber);

        abort_unless($booking->user_id === auth()->id() || auth()->user()->isSuperAdmin(), 403);

        $booking->loadMissing(['hotel.images', 'rooms.roomType', 'payment', 'invoice']);

        return view('booking.show', compact('booking'));
    }

    /**
     * Cancel a booking.
     */
    public function cancel(string $bookingNumber, Request $request)
    {
        $booking = $this->bookingService->findByNumber($bookingNumber);

        abort_unless($booking->user_id === auth()->id(), 403);

        try {
            $this->bookingService->cancel($booking, $request->input('reason', ''));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['cancel' => $e->getMessage()]);
        }

        return redirect()->route('account.bookings')
            ->with('success', "Booking #{$booking->booking_number} has been cancelled.");
    }

    /**
     * Download booking invoice as PDF.
     */
    public function invoice(string $bookingNumber)
    {
        $booking = $this->bookingService->findByNumber($bookingNumber);

        abort_unless(
            $booking->user_id === auth()->id() || auth()->user()->isSuperAdmin(),
            403
        );

        $invoice = $booking->invoice;
        abort_if(is_null($invoice), 404, 'Invoice not found.');

        return $this->invoiceService->download($invoice);
    }
}
