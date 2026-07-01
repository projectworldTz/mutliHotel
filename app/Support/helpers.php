<?php

if (! function_exists('money')) {
    /**
     * Format an amount with a currency code, defaulting to the platform's
     * configured currency (config('app.currency')) instead of a hardcoded
     * symbol or code.
     */
    function money(float|int|null $amount, ?string $currency = null, int $decimals = 0): string
    {
        $currency ??= config('app.currency');

        return trim($currency . ' ' . number_format((float) $amount, $decimals));
    }
}
