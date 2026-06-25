<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payments\BankTransferGateway;
use App\Services\Payments\CashGateway;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Payments\PayPalGateway;
use App\Services\Payments\StripeGateway;

class PaymentService
{
    /** @var array<string, PaymentGatewayInterface> */
    private array $gateways;

    public function __construct(
        StripeGateway      $stripe,
        PayPalGateway      $paypal,
        BankTransferGateway $bank,
        CashGateway        $cash,
    ) {
        $this->gateways = [
            $stripe->getKey() => $stripe,
            $paypal->getKey() => $paypal,
            $bank->getKey()   => $bank,
            $cash->getKey()   => $cash,
        ];
    }

    /**
     * Return all registered gateway instances.
     *
     * @return array<string, PaymentGatewayInterface>
     */
    public function gateways(): array
    {
        return $this->gateways;
    }

    /**
     * Return a list of gateway keys and labels suitable for a select dropdown.
     */
    public function availableGateways(): array
    {
        return array_map(
            fn (PaymentGatewayInterface $g) => ['key' => $g->getKey(), 'label' => $g->getName()],
            $this->gateways
        );
    }

    /**
     * Resolve a gateway by its key.
     *
     * @throws \InvalidArgumentException for unknown gateways
     */
    public function gateway(string $key): PaymentGatewayInterface
    {
        if (! isset($this->gateways[$key])) {
            throw new \InvalidArgumentException("Payment gateway '{$key}' is not registered.");
        }

        return $this->gateways[$key];
    }

    /**
     * Initiate a payment for a booking via the specified gateway.
     * Returns the gateway response array.
     */
    public function initiate(Booking $booking, string $gatewayKey, array $data = []): array
    {
        $gateway  = $this->gateway($gatewayKey);
        $response = $gateway->initiate($booking, $data);

        return $response;
    }

    /**
     * Verify and complete a payment (called from webhook or return URL).
     */
    public function verify(Payment $payment, array $gatewayData): array
    {
        $gateway  = $this->gateway($payment->method);
        $response = $gateway->verify($payment, $gatewayData);

        return $response;
    }

    /**
     * Issue a refund (full or partial) for a paid booking.
     *
     * @throws \RuntimeException when booking has no paid payment
     */
    public function refund(Booking $booking, float $amount = 0.0): array
    {
        $payment = Payment::where('booking_id', $booking->id)->paid()->first();

        if (! $payment) {
            throw new \RuntimeException("No paid payment found for booking #{$booking->booking_number}.");
        }

        $refundAmount = $amount > 0 ? $amount : (float) $payment->amount;
        $gateway      = $this->gateway($payment->method);

        return $gateway->refund($payment, $refundAmount);
    }

    /**
     * Look up an existing payment for a booking.
     */
    public function forBooking(Booking $booking): ?Payment
    {
        return Payment::where('booking_id', $booking->id)->latest()->first();
    }
}
