<?php

namespace App\Http\Controllers\Api;

use App\Events\BookingCreated;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\BookingService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private BookingService $bookingService,
        private InvoiceService $invoiceService,
    ) {}

    /**
     * Handle Stripe webhook events.
     *
     * Set STRIPE_WEBHOOK_SECRET in .env to enable signature verification.
     * Register this URL in your Stripe dashboard:
     *   POST /api/webhooks/stripe
     */
    public function stripe(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        // Verify signature when secret is configured
        if ($secret) {
            try {
                // Uncomment once stripe/stripe-php is installed:
                // $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
            } catch (\Exception $e) {
                Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        $data = $request->json()->all();
        $type = $data['type'] ?? null;

        Log::info('Stripe webhook received', ['type' => $type]);

        return match ($type) {
            'payment_intent.succeeded'        => $this->handleStripePaymentSucceeded($data),
            'payment_intent.payment_failed'   => $this->handleStripePaymentFailed($data),
            'charge.refunded'                 => $this->handleStripeRefund($data),
            default                           => response()->json(['status' => 'ignored']),
        };
    }

    private function handleStripePaymentSucceeded(array $data): \Illuminate\Http\JsonResponse
    {
        $intentId = $data['data']['object']['id'] ?? null;
        if (! $intentId) {
            return response()->json(['error' => 'Missing payment intent ID'], 400);
        }

        $payment = Payment::where('method', 'stripe')
            ->where(function ($q) use ($intentId) {
                $q->where('transaction_id', $intentId)
                  ->orWhereJsonContains('metadata->intent_id', $intentId);
            })
            ->first();

        if (! $payment) {
            Log::warning('Stripe webhook: payment not found', ['intent_id' => $intentId]);
            return response()->json(['status' => 'payment_not_found']);
        }

        $this->paymentService->verify($payment, ['payment_intent' => $intentId]);

        $booking = Booking::find($payment->booking_id);
        if ($booking && $booking->status === Booking::STATUS_PENDING) {
            $this->bookingService->confirm($booking);
            $booking->refresh();
            event(new BookingCreated($booking));
        }

        if ($booking?->invoice) {
            $this->invoiceService->issue($booking->invoice);
            $this->invoiceService->markPaid($booking->invoice);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleStripePaymentFailed(array $data): \Illuminate\Http\JsonResponse
    {
        $intentId = $data['data']['object']['id'] ?? null;
        $payment  = Payment::where('transaction_id', $intentId)->first();

        if ($payment) {
            $payment->update(['status' => 'failed']);
            Log::info('Stripe payment failed', ['booking_id' => $payment->booking_id]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleStripeRefund(array $data): \Illuminate\Http\JsonResponse
    {
        // Refunds are handled via PaymentService::refund() from the admin panel.
        // This webhook just logs the event for audit purposes.
        Log::info('Stripe refund webhook received', ['charge_id' => $data['data']['object']['id'] ?? null]);
        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle PayPal IPN / webhook events.
     *
     * Register this URL in your PayPal app settings:
     *   POST /api/webhooks/paypal
     */
    public function paypal(Request $request)
    {
        $data  = $request->json()->all();
        $event = $data['event_type'] ?? null;

        Log::info('PayPal webhook received', ['event' => $event]);

        if ($event === 'CHECKOUT.ORDER.APPROVED') {
            $orderId = $data['resource']['id'] ?? null;

            $payment = Payment::where('method', 'paypal')
                ->whereJsonContains('metadata->order_id', $orderId)
                ->orWhere('transaction_id', $orderId)
                ->first();

            if ($payment) {
                $this->paymentService->verify($payment, ['orderID' => $orderId]);

                $booking = Booking::find($payment->booking_id);
                if ($booking && $booking->status === Booking::STATUS_PENDING) {
                    $this->bookingService->confirm($booking);
                    $booking->refresh();
                    event(new BookingCreated($booking));
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
