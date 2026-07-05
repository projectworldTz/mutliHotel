<?php

namespace App\Http\Requests;

use App\Rules\WithinBookingAdvanceWindow;
use Illuminate\Foundation\Http\FormRequest;

class CheckAvailabilityRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'check_in'  => ['required', 'date', new WithinBookingAdvanceWindow],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests'    => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'check_out.after' => 'Check-out must be after check-in.',
        ];
    }
}
