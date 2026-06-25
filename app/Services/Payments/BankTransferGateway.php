<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Transaction;

/**
 * Bank transfer gateway.
 * Returns bank account details; payment is marked pending until admin confirms.
 */
class BankTransferGateway implements PaymentGatewayInterface
{
    public function getName(): string { return 'Bank Transfer'; }
    public function getKey(): string  { return 'bank'; }

    public function initiate(Booking $booking, array $data): array
    {
        $bankDetails = [
            'account_name'   => Setting::get('bank_account_name', 'Hotel Platform Ltd'),
            'account_number' => Setting::get('bank_account_number', 'XXXX-XXXX-XXXX'),
            'bank_name'      => Setting::get('bank_name', 'National Bank'),
            'swift_code'     => Setting::get('bank_swift_code', 'XXXXXXXX'),
            'reference'      => $booking->booking_number,
        ];

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'method'     => 'bank',
            'status'     => 'pending',
            'amount'     => $booking->grand_total,
            'currency'   => $booking->currency,
            'metadata'   => $bankDetails,
        ]);

        return [
            'success'       => true,
            'payment_id'    => $payment->id,
            'redirect_url'  => null,
            'client_secret' => null,
            'bank_details'  => $bankDetails,
            'message'       => 'Please transfer the amount to the account below. Use your booking number as the reference.',
        ];
    }

    public function verify(Payment $payment, array $gatewayData): array
    {
        $payment->update([
            'status'         => 'paid',
            'transaction_id' => $gatewayData['reference'] ?? $payment->booking->booking_number,
        ]);

        Transaction::create([
            'booking_id'             => $payment->booking_id,
            'payment_id'             => $payment->id,
            'type'                   => 'charge',
            'amount'                 => $payment->amount,
            'currency'               => $payment->currency,
            'status'                 => 'success',
            'gateway'                => 'bank',
            'gateway_transaction_id' => $gatewayData['reference'] ?? null,
            'gateway_response'       => $gatewayData,
        ]);

        return ['success' => true, 'message' => 'Bank transfer confirmed.'];
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
            'gateway'    => 'bank',
        ]);

        if ($amount >= $payment->amount) {
            $payment->update(['status' => 'refunded']);
        }

        return ['success' => true, 'refund_id' => null, 'message' => "Bank refund of \${$amount} initiated."];
    }
}
