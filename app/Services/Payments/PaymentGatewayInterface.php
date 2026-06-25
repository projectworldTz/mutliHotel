<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment for a booking.
     * Returns an array that may include a redirect_url, client_secret, or
     * gateway-specific data the frontend needs to complete the payment.
     *
     * @return array{
     *   success: bool,
     *   payment_id: int|null,
     *   redirect_url: string|null,
     *   client_secret: string|null,
     *   message: string
     * }
     */
    public function initiate(Booking $booking, array $data): array;

    /**
     * Verify and complete a payment using the gateway callback/webhook data.
     * Marks the Payment as paid if successful.
     *
     * @return array{success: bool, message: string}
     */
    public function verify(Payment $payment, array $gatewayData): array;

    /**
     * Issue a full or partial refund.
     *
     * @return array{success: bool, refund_id: string|null, message: string}
     */
    public function refund(Payment $payment, float $amount): array;

    /** Human-readable gateway name (e.g. "Stripe", "PayPal"). */
    public function getName(): string;

    /** Gateway identifier key (e.g. "stripe", "paypal", "cash"). */
    public function getKey(): string;
}
