<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payments\AirtelMoneyGateway;
use App\Services\Payments\DpoPayGateway;
use App\Services\Payments\HalotelGateway;
use App\Services\Payments\MixByYasGateway;
use App\Services\Payments\MpesaGateway;
use App\Services\Payments\PaymentGatewayInterface;

class PaymentService
{
    /** @var array<string, PaymentGatewayInterface> */
    private array $gateways;

    public function __construct(
        AirtelMoneyGateway $airtelMoney,
        MpesaGateway       $mpesa,
        HalotelGateway     $halotel,
        MixByYasGateway    $mixByYas,
        DpoPayGateway      $dpoCard,
    ) {
        $this->gateways = [
            $airtelMoney->getKey() => $airtelMoney,
            $mpesa->getKey()       => $mpesa,
            $halotel->getKey()     => $halotel,
            $mixByYas->getKey()    => $mixByYas,
            $dpoCard->getKey()     => $dpoCard,
        ];
    }

    /** @return array<string, PaymentGatewayInterface> */
    public function gateways(): array
    {
        return $this->gateways;
    }

    /** Return gateway keys and labels for a select dropdown. */
    public function availableGateways(): array
    {
        return array_map(
            fn (PaymentGatewayInterface $g) => ['key' => $g->getKey(), 'label' => $g->getName()],
            $this->gateways
        );
    }

    /** @throws \InvalidArgumentException for unknown gateways */
    public function gateway(string $key): PaymentGatewayInterface
    {
        if (! isset($this->gateways[$key])) {
            throw new \InvalidArgumentException("Payment gateway '{$key}' is not registered.");
        }

        return $this->gateways[$key];
    }

    public function initiate(Booking $booking, string $gatewayKey, array $data = []): array
    {
        return $this->gateway($gatewayKey)->initiate($booking, $data);
    }

    public function verify(Payment $payment, array $gatewayData): array
    {
        return $this->gateway($payment->method)->verify($payment, $gatewayData);
    }

    /** @throws \RuntimeException when booking has no paid payment */
    public function refund(Booking $booking, float $amount = 0.0): array
    {
        $payment = Payment::where('booking_id', $booking->id)->paid()->first();

        if (! $payment) {
            throw new \RuntimeException("No paid payment found for booking #{$booking->booking_number}.");
        }

        $refundAmount = $amount > 0 ? $amount : (float) $payment->amount;

        return $this->gateway($payment->method)->refund($payment, $refundAmount);
    }

    public function forBooking(Booking $booking): ?Payment
    {
        return Payment::where('booking_id', $booking->id)->latest()->first();
    }
}
