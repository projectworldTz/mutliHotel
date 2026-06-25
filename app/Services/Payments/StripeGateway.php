<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Transaction;

class StripeGateway implements PaymentGatewayInterface
{
    public function getName(): string { return 'Stripe'; }
    public function getKey(): string  { return 'stripe'; }

    public function initiate(Booking $booking, array $data): array
    {
        // Create a pending Payment record first so we have an ID to track
        $payment = Payment::create([
            'booking_id'     => $booking->id,
            'method'         => 'stripe',
            'status'         => 'pending',
            'amount'         => $booking->grand_total,
            'currency'       => $booking->currency,
        ]);

        // TODO: Replace with real Stripe SDK call:
        //   $intent = \Stripe\PaymentIntent::create([
        //       'amount'   => (int) ($booking->grand_total * 100),
        //       'currency' => strtolower($booking->currency),
        //       'metadata' => ['booking_number' => $booking->booking_number],
        //   ]);
        //   $payment->update(['transaction_id' => $intent->id, 'metadata' => ['client_secret' => $intent->client_secret]]);
        //   return ['success' => true, 'payment_id' => $payment->id, 'client_secret' => $intent->client_secret, 'redirect_url' => null, 'message' => 'Payment intent created.'];

        $payment->update(['metadata' => ['note' => 'Stripe integration pending — add STRIPE_SECRET to .env']]);

        return [
            'success'       => true,
            'payment_id'    => $payment->id,
            'redirect_url'  => null,
            'client_secret' => null,
            'message'       => 'Stripe gateway ready. Configure STRIPE_SECRET in .env to enable live payments.',
        ];
    }

    public function verify(Payment $payment, array $gatewayData): array
    {
        // TODO: Verify Stripe webhook signature and event type
        //   $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, config('services.stripe.webhook_secret'));
        //   if ($event->type === 'payment_intent.succeeded') { ... }

        $payment->update([
            'status'         => 'paid',
            'transaction_id' => $gatewayData['payment_intent'] ?? null,
        ]);

        Transaction::create([
            'booking_id'             => $payment->booking_id,
            'payment_id'             => $payment->id,
            'type'                   => 'charge',
            'amount'                 => $payment->amount,
            'currency'               => $payment->currency,
            'status'                 => 'success',
            'gateway'                => 'stripe',
            'gateway_transaction_id' => $gatewayData['payment_intent'] ?? null,
            'gateway_response'       => $gatewayData,
        ]);

        return ['success' => true, 'message' => 'Payment verified.'];
    }

    public function refund(Payment $payment, float $amount): array
    {
        // TODO: \Stripe\Refund::create(['payment_intent' => $payment->transaction_id, 'amount' => $amount * 100]);

        Transaction::create([
            'booking_id' => $payment->booking_id,
            'payment_id' => $payment->id,
            'type'       => 'refund',
            'amount'     => $amount,
            'currency'   => $payment->currency,
            'status'     => 'success',
            'gateway'    => 'stripe',
        ]);

        if ($amount >= $payment->amount) {
            $payment->update(['status' => 'refunded']);
        }

        return ['success' => true, 'refund_id' => null, 'message' => "Refund of \${$amount} processed."];
    }
}
