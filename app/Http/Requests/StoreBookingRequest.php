<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'guests_adults'    => ['required', 'integer', 'min:1', 'max:20'],
            'guests_children'  => ['integer', 'min:0', 'max:10'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'coupon_code'      => ['nullable', 'string', 'max:50'],
            'payment_method'   => ['required', 'in:stripe,paypal,bank,cash'],
            'agree_terms'      => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'agree_terms.accepted' => 'You must accept the terms and conditions.',
            'payment_method.in'    => 'Please select a valid payment method.',
        ];
    }
}
