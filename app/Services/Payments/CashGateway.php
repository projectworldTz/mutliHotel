<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Transaction;

/**
 * Cash / pay-at-hotel gateway.
 * Marks the payment as "pending" at booking time; the receptionist confirms
 * it as "paid" manually via the hotel dashboard on check-in.
 */
class CashGateway implements PaymentGatewayInterface
{
    public function getName(): string { return 'Cash / Pay at Hotel'; }
    public function getKey(): string  { return 'cash'; }

    public function initiate(Booking $booking, array $data): array
    {
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'method'     => 'cash',
            'status'     => 'pending',
            'amount'     => $booking->grand_total,
            'currency'   => $booking->currency,
            'metadata'   => ['note' => 'Guest will pay at check-in'],
        ]);

        return [
            'success'       => true,
            'payment_id'    => $payment->id,
            'redirect_url'  => null,
            'client_secret' => null,
            'message'       => 'Booking created. You will pay at the hotel on arrival.',
        ];
    }

    public function verify(Payment $payment, array $gatewayData): array
    {
        // Called by the receptionist/hotel owner when cash is received
        $payment->update([
            'status'         => 'paid',
            'transaction_id' => 'CASH-' . now()->format('YmdHis'),
        ]);

        Transaction::create([
            'booking_id' => $payment->booking_id,
            'payment_id' => $payment->id,
            'type'       => 'charge',
            'amount'     => $payment->amount,
            'currency'   => $payment->currency,
            'status'     => 'success',
            'gateway'    => 'cash',
        ]);

        return ['success' => true, 'message' => 'Cash payment recorded.'];
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
            'gateway'    => 'cash',
        ]);

        if ($amount >= $payment->amount) {
            $payment->update(['status' => 'refunded']);
        }

        return ['success' => true, 'refund_id' => null, 'message' => "Cash refund of \${$amount} issued."];
    }
}
