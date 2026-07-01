<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class HalotelGateway implements PaymentGatewayInterface
{
    public function getName(): string { return 'Halotel'; }
    public function getKey(): string  { return 'halotel'; }

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
                'provider'     => 'halotel',
                'initiated_at' => now()->toISOString(),
            ],
        ]);

        // TODO: Call real Halotel HaloPesa API when credentials are available
        // $response = Http::withHeaders([
        //     'Authorization' => 'Basic ' . base64_encode(config('payments.halotel.username') . ':' . config('payments.halotel.password')),
        //     'Content-Type'  => 'application/json',
        // ])->post(config('payments.halotel.base_url') . '/api/payment/request', [
        //     'msisdn'    => ltrim($phone, '+0'),
        //     'amount'    => $booking->grand_total,
        //     'reference' => $booking->booking_number,
        //     'callback'  => route('api.webhooks.mobile-money', ['provider' => 'halotel']),
        // ]);

        Log::info('Halotel payment initiated', [
            'booking'  => $booking->booking_number,
            'phone'    => $phone,
            'amount'   => $booking->grand_total,
        ]);

        return [
            'success'    => true,
            'payment_id' => $payment->id,
            'phone'      => $phone,
            'message'    => "A payment request of TZS " . number_format($booking->grand_total, 0) . " has been sent to {$phone}. Enter your Halotel PIN to complete.",
        ];
    }

    public function verify(Payment $payment, array $gatewayData): array
    {
        $payment->update([
            'status'         => 'paid',
            'transaction_id' => $gatewayData['transaction_id'] ?? ('HL-' . now()->format('YmdHis')),
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

        return ['success' => true, 'message' => 'Halotel payment confirmed.'];
    }

    public function refund(Payment $payment, float $amount): array
    {
        // TODO: Call Halotel refund API when credentials are available
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

        return ['success' => true, 'refund_id' => null, 'message' => "Halotel refund of TZS " . number_format($amount, 0) . " initiated."];
    }
}
