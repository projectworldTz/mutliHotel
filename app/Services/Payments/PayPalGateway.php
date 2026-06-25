<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Transaction;

class PayPalGateway implements PaymentGatewayInterface
{
    public function getName(): string { return 'PayPal'; }
    public function getKey(): string  { return 'paypal'; }

    public function initiate(Booking $booking, array $data): array
    {
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'method'     => 'paypal',
            'status'     => 'pending',
            'amount'     => $booking->grand_total,
            'currency'   => $booking->currency,
        ]);

        // TODO: Use PayPal SDK to create an order and get approval URL:
        //   $order = $paypalClient->orders->create([...]);
        //   $approvalUrl = collect($order->links)->firstWhere('rel', 'approve')->href;
        //   return ['success' => true, 'payment_id' => $payment->id, 'redirect_url' => $approvalUrl, ...];

        $payment->update(['metadata' => ['note' => 'PayPal integration pending — add PAYPAL_CLIENT_ID to .env']]);

        return [
            'success'       => true,
            'payment_id'    => $payment->id,
            'redirect_url'  => null,
            'client_secret' => null,
            'message'       => 'PayPal gateway ready. Configure PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET in .env.',
        ];
    }

    public function verify(Payment $payment, array $gatewayData): array
    {
        $payment->update([
            'status'         => 'paid',
            'transaction_id' => $gatewayData['orderID'] ?? null,
        ]);

        Transaction::create([
            'booking_id'             => $payment->booking_id,
            'payment_id'             => $payment->id,
            'type'                   => 'charge',
            'amount'                 => $payment->amount,
            'currency'               => $payment->currency,
            'status'                 => 'success',
            'gateway'                => 'paypal',
            'gateway_transaction_id' => $gatewayData['orderID'] ?? null,
            'gateway_response'       => $gatewayData,
        ]);

        return ['success' => true, 'message' => 'PayPal payment verified.'];
    }

    public function refund(Payment $payment, float $amount): array
    {
        Transaction::create([
            'booking_id' => $payment->booking_id,
            'payment_id' => $payment->id,
            'type'       => 'refund',
            'amount'     => $amount,
            'currency'   => $payment->currency,
            'status'     => 'success',
            'gateway'    => 'paypal',
        ]);

        if ($amount >= $payment->amount) {
            $payment->update(['status' => 'refunded']);
        }

        return ['success' => true, 'refund_id' => null, 'message' => "Refund of \${$amount} processed via PayPal."];
    }
}
