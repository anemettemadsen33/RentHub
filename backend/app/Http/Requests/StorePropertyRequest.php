<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Map legacy field names to current schema
            'price_per_night' => $this->input('price_per_night', $this->input('price')),
            'guests' => $this->input('guests', $this->input('max_guests')),
            'street_address' => $this->input('street_address', $this->input('address')),
        ]);
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Debug log for tests
        if (app()->environment('testing')) {
            \Log::debug('StorePropertyRequest authorize', [
                'user_exists' => $this->user() !== null,
                'user_id' => $this->user()?->id,
                'user_roles' => $this->user()?->roles->pluck('name')->toArray() ?? [],
                'has_owner' => $this->user()?->hasRole('owner') ?? false,
                'has_host' => $this->user()?->hasRole('host') ?? false,
                'has_admin' => $this->user()?->hasRole('admin') ?? false,
                'has_any' => $this->user()?->hasAnyRole(['owner', 'host', 'admin']) ?? false,
            ]);
        }
        
        // Only owners/hosts and admins can create properties
        return $this->user() && $this->user()->hasAnyRole(['owner', 'host', 'admin']);
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'min:50'],
            'type' => ['required', 'in:apartment,house,villa,studio,condo,townhouse,loft,guesthouse'],

            // Property details
            'bedrooms' => ['required', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['required', 'integer', 'min:1', 'max:50'],
            'guests' => ['required', 'integer', 'min:1', 'max:50'],
            'min_nights' => ['nullable', 'integer', 'min:1', 'max:365'],
            'max_nights' => ['nullable', 'integer', 'min:1', 'max:365', 'gte:min_nights'],
            'area_sqm' => ['nullable', 'numeric', 'min:1'],
            'built_year' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y')],

            // Address (required by schema)
            'street_address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // Pricing
            // Legacy tests may send 'price' instead of 'price_per_night'
            'price_per_night' => ['required_without:price', 'numeric', 'min:1'],
            'price' => ['required_without:price_per_night', 'numeric', 'min:1'],
            'price_per_week' => ['nullable', 'numeric', 'min:1'],
            'price_per_month' => ['nullable', 'numeric', 'min:1'],
            'cleaning_fee' => ['nullable', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],

            // Property rules
            'rules' => ['nullable', 'array'],
            'rules.*.title' => ['required', 'string'],
            'rules.*.description' => ['nullable', 'string'],

            // Availability
            'available_from' => ['nullable', 'date', 'after_or_equal:today'],
            'available_until' => ['nullable', 'date', 'after:available_from'],
            'blocked_dates' => ['nullable', 'array'],
            'blocked_dates.*' => ['date'],
            'custom_pricing' => ['nullable', 'array'],

            // Status (aligned with updated enum: available/booked/maintenance)
            'status' => ['nullable', 'in:available,booked,maintenance'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],

            // Amenities
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['exists:amenities,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Property title is required',
            'description.required' => 'Property description is required',
            'description.min' => 'Description must be at least 50 characters',
            'type.required' => 'Property type is required',
            'price_per_night.required' => 'Price per night is required',
            'street_address.required' => 'Street address is required',
            'city.required' => 'City is required',
            'country.required' => 'Country is required',
            'max_nights.gte' => 'Maximum nights must be greater than or equal to minimum nights',
        ];
    }
}
