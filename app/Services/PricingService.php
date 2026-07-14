<?php

namespace App\Services;

use App\Models\RoomType;
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
     * Build a full order total from a room subtotal plus any meal-package
     * add-ons total. Taxation is disabled.
     *
     * Returns:
     *   subtotal        float   — room cost only, unchanged meaning
     *   addons_total    float   — meal packages / add-ons
     *   tax_rate        float
     *   tax_total       float
     *   discount_total  float
     *   grand_total     float
     */
    public function calculateOrderTotal(float $subtotal, float $addonsTotal = 0.0): array
    {
        return [
            'subtotal'       => $subtotal,
            'addons_total'   => $addonsTotal,
            'tax_rate'       => 0.0,
            'tax_total'      => 0.0,
            'discount_total' => 0.0,
            'grand_total'    => max(0, round($subtotal + $addonsTotal, 2)),
        ];
    }

    /**
     * Format a decimal as a currency string.
     */
    public function format(float $amount, ?string $currency = null): string
    {
        return money($amount, $currency, 2);
    }
}
