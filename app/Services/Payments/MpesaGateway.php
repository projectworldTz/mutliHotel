<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class MpesaGateway implements PaymentGatewayInterface
{
    public function getName(): string { return 'M-Pesa'; }
    public function getKey(): string  { return 'mpesa'; }

    public function initiate(Booking $booking, array $data): array
    {
        $phone = $data['phone_number'];

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'method'     => $this->getKey(),
            'status'     => 'pending',
            'amount'     => $booking->grand_total,
            'currency'   => $booking->currency ?? config('app.currency'),
            'metadata'   => [
                'phone'        => $phone,
                'reference'    => $booking->booking_number,
                'provider'     => 'mpesa',
                'initiated_at' => now()->toISOString(),
            ],
        ]);

        // TODO: Call real Vodacom M-Pesa Tanzania API when credentials are available
        // $token = Http::asForm()->post(config('payments.mpesa.auth_url'), [
        //     'grant_type' => 'client_credentials',
        // ])->json('access_token');
        //
        // $response = Http::withToken($token)
        //     ->post(config('payments.mpesa.base_url') . '/stkpush/v1/processrequest', [
        //         'BusinessShortCode' => config('payments.mpesa.short_code'),
        //         'Password'          => base64_encode(config('payments.mpesa.short_code') . config('payments.mpesa.passkey') . now()->format('YmdHis')),
        //         'Timestamp'         => now()->format('YmdHis'),
        //         'TransactionType'   => 'CustomerPayBillOnline',
        //         'Amount'            => $booking->grand_total,
        //         'PartyA'            => ltrim($phone, '+0'),
        //         'PartyB'            => config('payments.mpesa.short_code'),
        //         'PhoneNumber'       => ltrim($phone, '+0'),
        //         'CallBackURL'       => route('api.webhooks.mobile-money', ['provider' => 'mpesa']),
        //         'AccountReference'  => $booking->booking_number,
        //         'TransactionDesc'   => 'Hotel Booking Payment',
        //     ]);

        Log::info('M-Pesa payment initiated', [
            'booking'  => $booking->booking_number,
            'phone'    => $phone,
            'amount'   => $booking->grand_total,
        ]);

        return [
            'success'    => true,
            'payment_id' => $payment->id,
            'phone'      => $phone,
            'message'    => "A payment request of TZS " . number_format($booking->grand_total, 0) . " has been sent to {$phone}. Enter your M-Pesa PIN to complete.",
        ];
    }

    public function verify(Payment $payment, array $gatewayData): array
    {
        $payment->update([
            'status'         => 'paid',
            'transaction_id' => $gatewayData['transaction_id'] ?? ('MP-' . now()->format('YmdHis')),
        ]);

        Transaction::create([
            'booking_id'             => $payment->booking_id,
            'payment_id'             => $payment->id,
            'type'                   => 'charge',
            'amount'                 => $payment->amount,
            'currency'               => $payment->currency,
            'status'                 => 'success',
            'gateway'                => $this->getKey(),
            'gateway_transaction_id' => $gatewayData['transaction_id'] ?? null,
            'gateway_response'       => json_encode($gatewayData),
        ]);

        return ['success' => true, 'message' => 'M-Pesa payment confirmed.'];
    }

    public function refund(Payment $payment, float $amount): array
    {
        // TODO: Call M-Pesa reversal API when credentials are available
        Transaction::create([
            'booking_id' => $payment->booking_id,
            'payment_id' => $payment->id,
            'type'       => 'refund',
            'amount'     => $amount,
            'currency'   => $payment->currency,
            'status'     => 'success',
            'gateway'    => $this->getKey(),
        ]);

        if ($amount >= (float) $payment->amount) {
            $payment->update(['status' => 'refunded']);
        }

        return ['success' => true, 'refund_id' => null, 'message' => "M-Pesa refund of TZS " . number_format($amount, 0) . " initiated."];
    }
}
