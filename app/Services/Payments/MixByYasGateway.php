<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class MixByYasGateway implements PaymentGatewayInterface
{
    public function getName(): string { return 'Mix by Yas'; }
    public function getKey(): string  { return 'mix_by_yas'; }

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
                'provider'     => 'mix_by_yas',
                'initiated_at' => now()->toISOString(),
            ],
        ]);

        // TODO: Call real Mix by Yas (formerly Tigo Pesa) API when credentials are available
        // $response = Http::withHeaders([
        //     'Authorization' => 'Bearer ' . config('payments.mix_by_yas.api_key'),
        //     'Content-Type'  => 'application/json',
        // ])->post(config('payments.mix_by_yas.base_url') . '/v1/payment/collect', [
        //     'CustomerMSISDN' => ltrim($phone, '+0'),
        //     'BillerMSISDN'   => config('payments.mix_by_yas.biller_msisdn'),
        //     'Amount'         => $booking->grand_total,
        //     'Remarks'        => $booking->booking_number,
        //     'ReferenceID'    => $booking->booking_number,
        //     'SenderName'     => $booking->user->name ?? 'Guest',
        // ]);

        Log::info('Mix by Yas payment initiated', [
            'booking'  => $booking->booking_number,
            'phone'    => $phone,
            'amount'   => $booking->grand_total,
        ]);

        return [
            'success'    => true,
            'payment_id' => $payment->id,
            'phone'      => $phone,
            'message'    => "A payment request of TZS " . number_format($booking->grand_total, 0) . " has been sent to {$phone}. Enter your Mix by Yas PIN to complete.",
        ];
    }

    public function verify(Payment $payment, array $gatewayData): array
    {
        $payment->update([
            'status'         => 'paid',
            'transaction_id' => $gatewayData['transaction_id'] ?? ('MX-' . now()->format('YmdHis')),
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

        return ['success' => true, 'message' => 'Mix by Yas payment confirmed.'];
    }

    public function refund(Payment $payment, float $amount): array
    {
        // TODO: Call Mix by Yas refund API when credentials are available
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

        return ['success' => true, 'refund_id' => null, 'message' => "Mix by Yas refund of TZS " . number_format($amount, 0) . " initiated."];
    }
}
