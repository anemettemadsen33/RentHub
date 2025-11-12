<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\PropertyRuleSets;

class StorePropertyRequest extends FormRequest
{
    use PropertyRuleSets;

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
        return array_merge(
            $this->baseInfoRules(false),
            $this->detailsRules(false),
            $this->addressRules(false),
            $this->pricingRules(false),
            $this->rulesRules(),
            $this->availabilityRules(),
            $this->statusRules(false),
            $this->amenitiesRules(),
        );
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
