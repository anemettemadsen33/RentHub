<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $property = $this->route('property');

        // Admin can update any property
        if ($this->user()->hasRole('admin')) {
            return true;
        }

        // Owner/Host can only update their own properties
        return $property && $property->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Basic info
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'min:50'],
            'type' => ['sometimes', 'in:apartment,house,villa,studio,condo,townhouse,loft,guesthouse'],

            // Property details
            'bedrooms' => ['sometimes', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'guests' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'min_nights' => ['nullable', 'integer', 'min:1', 'max:365'],
            'max_nights' => ['nullable', 'integer', 'min:1', 'max:365', 'gte:min_nights'],
            'area_sqm' => ['nullable', 'numeric', 'min:1'],
            'built_year' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y')],

            // Address
            'street_address' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['sometimes', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // Pricing
            'price_per_night' => ['sometimes', 'numeric', 'min:1'],
            'price' => ['sometimes', 'numeric', 'min:1'],
            'price_per_week' => ['nullable', 'numeric', 'min:1'],
            'price_per_month' => ['nullable', 'numeric', 'min:1'],
            'cleaning_fee' => ['nullable', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],

            // Property rules
            'rules' => ['nullable', 'array'],
            'rules.*.title' => ['required', 'string'],
            'rules.*.description' => ['nullable', 'string'],

            // Availability
            'available_from' => ['nullable', 'date'],
            'available_until' => ['nullable', 'date', 'after:available_from'],
            'blocked_dates' => ['nullable', 'array'],
            'blocked_dates.*' => ['date'],
            'custom_pricing' => ['nullable', 'array'],

            // Status (aligned with updated enum)
            'status' => ['sometimes', 'in:available,booked,maintenance'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],

            // Amenities
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['exists:amenities,id'],
        ];
    }
}
