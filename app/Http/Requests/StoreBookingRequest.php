<?php

namespace App\Http\Requests;

use App\Models\ReservationCart;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('phone_number')) {
            return;
        }

        // Normalize: strip country code, spaces, dashes, then strip leading 0
        $phone = preg_replace('/[\s\-\(\)\+]/', '', $this->input('phone_number', ''));
        if (str_starts_with($phone, '255')) {
            $phone = substr($phone, 3);
        }
        $phone = ltrim($phone, '0');
        $this->merge(['phone_number' => $phone]);
    }

    public function rules(): array
    {
        $base = [
            'guests_adults'    => ['required', 'integer', 'min:1', 'max:20'],
            'guests_children'  => ['integer', 'min:0', 'max:10'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'agree_terms'      => ['required', 'accepted'],
        ];

        if ($this->resolvedHotel()?->manual_payment_enabled) {
            return $base;
        }

        $base += [
            'payment_method' => ['required', 'in:airtel_money,mpesa,halotel,mix_by_yas,dpo_card'],
        ];

        // Card payments redirect to a hosted page — no Tanzanian mobile number needed.
        if ($this->input('payment_method') === 'dpo_card') {
            return $base;
        }

        return $base + [
            'phone_number' => ['required', 'digits:9', 'regex:/^[67]\d{8}$/', $this->phoneNetworkRule()],
        ];
    }

    /** Resolve the hotel behind the user's cart, same path used by BookingController::checkout(). */
    private function resolvedHotel(): ?\App\Models\Hotel
    {
        return ReservationCart::where('user_id', $this->user()->id)
            ->with('items.roomType.hotel')
            ->first()
            ?->items->first()?->roomType?->hotel;
    }

    private function phoneNetworkRule(): \Closure
    {
        $prefixMap = [
            'airtel_money' => ['68', '69', '78'],
            'mpesa'        => ['74', '75', '76'],
            'halotel'      => ['62'],
            'mix_by_yas'   => ['71'],
        ];

        $hintMap = [
            'airtel_money' => 'Airtel Money (68x, 69x, 78x)',
            'mpesa'        => 'M-Pesa (74x, 75x, 76x)',
            'halotel'      => 'Halotel (62x)',
            'mix_by_yas'   => 'Mix by Yas (71x)',
        ];

        $method  = $this->input('payment_method');
        $allowed = $prefixMap[$method] ?? [];

        return function (string $attribute, mixed $value, \Closure $fail) use ($method, $allowed, $hintMap) {
            if (empty($allowed)) return;
            foreach ($allowed as $prefix) {
                if (str_starts_with($value, $prefix)) return;
            }
            $fail('This number is not registered on ' . ($hintMap[$method] ?? $method) . '.');
        };
    }

    public function messages(): array
    {
        return [
            'agree_terms.accepted'   => 'You must accept the terms and conditions.',
            'payment_method.in'      => 'Please select a valid payment method.',
            'phone_number.required'  => 'Please enter your mobile money phone number.',
            'phone_number.digits'    => 'Phone number must be exactly 9 digits after +255 (e.g. 712 345 678).',
            'phone_number.regex'     => 'Tanzanian numbers must start with 6 or 7.',
        ];
    }
}
