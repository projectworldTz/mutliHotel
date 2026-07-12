<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHotelRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        // <input type="time"> may submit "HH:MM:SS" — strip seconds to satisfy H:i validation
        foreach (['check_in_time', 'check_out_time'] as $field) {
            $val = $this->input($field);
            if ($val && preg_match('/^\d{2}:\d{2}:\d{2}$/', $val)) {
                $this->merge([$field => substr($val, 0, 5)]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'owner_id'            => [$this->routeIs('admin.hotels.store') ? 'required' : 'nullable', 'exists:users,id'],
            'name'                => ['required', 'string', 'max:255'],
            'hotel_category_id'   => ['nullable', 'exists:hotel_categories,id'],
            'description'         => ['nullable', 'string'],
            'short_description'   => ['nullable', 'string', 'max:500'],
            'address'             => ['required', 'string', 'max:255'],
            'city'                => ['required', 'string', 'max:100'],
            'state'               => ['nullable', 'string', 'max:100'],
            'country'             => ['required', 'string', 'max:100'],
            'postal_code'         => ['nullable', 'string', 'max:20'],
            'latitude'            => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'           => ['nullable', 'numeric', 'between:-180,180'],
            'star_rating'         => ['required', 'integer', 'min:1', 'max:5'],
            'phone'               => ['nullable', 'string', 'max:30'],
            'email'               => ['nullable', 'email', 'max:255'],
            'website'             => ['nullable', 'url', 'max:255'],
            'check_in_time'       => ['required', 'date_format:H:i'],
            'check_out_time'      => ['required', 'date_format:H:i'],
            'cancellation_policy' => ['nullable', 'string'],
            'amenity_ids'         => ['nullable', 'array'],
            'amenity_ids.*'       => ['integer', 'exists:amenities,id'],
            'images'              => ['nullable', 'array', 'max:8'],
            'images.*'            => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}
