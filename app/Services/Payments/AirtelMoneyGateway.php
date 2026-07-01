<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class AirtelMoneyGateway implements PaymentGatewayInterface
{
    public function getName(): string { return 'Airtel Money'; }
    public function getKey(): string  { return 'airtel_money'; }

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
                'provider'     => 'airtel_money',
                'initiated_at' => now()->toISOString(),
            ],
        ]);

        // TODO: Call real Airtel Money API when credentials are available
        // $response = Http::withHeaders([
        //     'Authorization' => 'Bearer ' . config('payments.airtel_money.api_key'),
        //     'Content-Type'  => 'application/json',
        //     'X-Country'     => 'TZ',
        //     'X-Currency'    => 'TZS',
        // ])->post(config('payments.airtel_money.base_url') . '/merchant/v1/payments/', [
        //     'reference'   => $booking->booking_number,
        //     'subscriber'  => ['country' => 'TZ', 'currency' => 'TZS', 'msisdn' => ltrim($phone, '+0')],
        //     'transaction' => ['amount' => $booking->grand_total, 'country' => 'TZ', 'currency' => 'TZS', 'id' => $payment->id],
        // ]);

        Log::info('Airtel Money payment initiated', [
            'booking'  => $booking->booking_number,
            'phone'    => $phone,
            'amount'   => $booking->grand_total,
        ]);

        return [
            'success'    => true,
            'payment_id' => $payment->id,
            'phone'      => $phone,
            'message'    => "A payment request of TZS " . number_format($booking->grand_total, 0) . " has been sent to {$phone}. Enter your Airtel Money PIN to complete.",
        ];
    }

    public function verify(Payment $payment, array $gatewayData): array
    {
        $payment->update([
            'status'         => 'paid',
            'transaction_id' => $gatewayData['transaction_id'] ?? ('AM-' . now()->format('YmdHis')),
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

        return ['success' => true, 'message' => 'Airtel Money payment confirmed.'];
    }

    public function refund(Payment $payment, float $amount): array
    {
        // TODO: Call Airtel Money refund API when credentials are available
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

        return ['success' => true, 'refund_id' => null, 'message' => "Airtel Money refund of TZS " . number_format($amount, 0) . " initiated."];
    }
}
