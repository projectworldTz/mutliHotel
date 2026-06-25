<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\RoomType;
use App\Models\Setting;
use Carbon\Carbon;

class PricingService
{
    /**
     * Calculate the full pricing breakdown for a room type over a stay window.
     *
     * Returns:
     *   nightly_rate   float    — average nightly rate across the stay
     *   nights         int
     *   subtotal       float    — nightly_rate × nights (pre-tax, pre-discount)
     */
    public function calculateForStay(RoomType $roomType, string $checkIn, string $checkOut): array
    {
        $checkInDate  = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);
        $nights       = $checkInDate->diffInDays($checkOutDate);

        if ($nights < 1) {
            return ['nightly_rate' => (float) $roomType->base_price, 'nights' => 0, 'subtotal' => 0.0];
        }

        $total = 0.0;
        $current = $checkInDate->copy();

        // Sum up per-night price applying seasonal overrides
        while ($current->lt($checkOutDate)) {
            $total += $this->nightlyRate($roomType, $current);
            $current->addDay();
        }

        $avgNightly = round($total / $nights, 2);

        return [
            'nightly_rate' => $avgNightly,
            'nights'       => $nights,
            'subtotal'     => round($total, 2),
        ];
    }

    /**
     * Resolve the effective price for a single night, checking seasonal overrides
     * ordered by priority (highest wins).
     */
    public function nightlyRate(RoomType $roomType, Carbon $date): float
    {
        $seasonal = $roomType->seasonalPrices()
            ->active()
            ->forDate($date->toDateString())
            ->orderByDesc('priority')
            ->first();

        if (! $seasonal) {
            return (float) $roomType->base_price;
        }

        return $seasonal->applyTo((float) $roomType->base_price);
    }

    /**
     * Build a full order total from a collection of cart item pricing arrays.
     *
     * Returns:
     *   subtotal        float
     *   tax_rate        float
     *   tax_total       float
     *   discount_total  float
     *   coupon_code     string|null
     *   grand_total     float
     */
    public function calculateOrderTotal(float $subtotal, ?Coupon $coupon = null): array
    {
        $taxRate  = (float) Setting::get('booking_tax_rate', 10);
        $taxTotal = round($subtotal * $taxRate / 100, 2);

        $discountTotal = 0.0;
        $couponCode    = null;

        if ($coupon && $coupon->isValidForAmount($subtotal)) {
            $discountTotal = $coupon->calculateDiscount($subtotal);
            $couponCode    = $coupon->code;
        }

        $grandTotal = max(0, round($subtotal + $taxTotal - $discountTotal, 2));

        return [
            'subtotal'       => $subtotal,
            'tax_rate'       => $taxRate,
            'tax_total'      => $taxTotal,
            'discount_total' => $discountTotal,
            'coupon_code'    => $couponCode,
            'grand_total'    => $grandTotal,
        ];
    }

    /**
     * Format a decimal as a currency string.
     */
    public function format(float $amount, string $currency = 'USD'): string
    {
        return '$' . number_format($amount, 2);
    }
}
