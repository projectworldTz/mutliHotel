<?php

namespace App\Rules;

use App\Models\Setting;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WithinBookingAdvanceWindow implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $minDays = (int) Setting::get('min_advance_days', 0);
        $maxDays = (int) Setting::get('max_advance_days', 365);

        $checkIn = Carbon::parse($value)->startOfDay();
        $today   = Carbon::today();

        if ($checkIn->lt($today->copy()->addDays($minDays))) {
            $fail($minDays > 0
                ? "Check-in must be at least {$minDays} day(s) from today."
                : 'Check-in date cannot be in the past.');

            return;
        }

        if ($checkIn->gt($today->copy()->addDays($maxDays))) {
            $fail("Check-in can be booked at most {$maxDays} days in advance.");
        }
    }
}
