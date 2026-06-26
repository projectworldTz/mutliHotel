<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'check_in'     => ['required', 'date', 'after_or_equal:today'],
            'check_out'    => ['required', 'date', 'after:check_in'],
            'guests'       => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'room_type_id.exists'     => 'The selected room type is no longer available.',
            'check_in.after_or_equal' => 'Check-in date cannot be in the past.',
            'check_out.after'         => 'Check-out must be after check-in.',
        ];
    }
}
