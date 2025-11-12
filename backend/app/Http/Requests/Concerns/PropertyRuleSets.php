<?php

namespace App\Http\Requests\Concerns;

trait PropertyRuleSets
{
    protected function req(bool $sometimes = false): string
    {
        return $sometimes ? 'sometimes' : 'required';
    }

    protected function baseInfoRules(bool $sometimes = false): array
    {
        return [
            'title' => [$this->req($sometimes), 'string', 'max:255'],
            'description' => [$sometimes ? 'sometimes' : 'nullable', 'string', 'min:50'],
            'type' => [$this->req($sometimes), 'in:apartment,house,villa,studio,condo,townhouse,loft,guesthouse'],
        ];
    }

    protected function detailsRules(bool $sometimes = false): array
    {
        return [
            'bedrooms' => [$this->req($sometimes), 'integer', 'min:0', 'max:50'],
            'bathrooms' => [$this->req($sometimes), 'integer', 'min:1', 'max:50'],
            'guests' => [$this->req($sometimes), 'integer', 'min:1', 'max:50'],
            'min_nights' => ['nullable', 'integer', 'min:1', 'max:365'],
            'max_nights' => ['nullable', 'integer', 'min:1', 'max:365', 'gte:min_nights'],
            'area_sqm' => ['nullable', 'numeric', 'min:1'],
            'built_year' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y')],
        ];
    }

    protected function addressRules(bool $sometimes = false): array
    {
        return [
            'street_address' => [$this->req($sometimes), 'string', 'max:255'],
            'city' => [$this->req($sometimes), 'string', 'max:100'],
            'state' => [$sometimes ? 'sometimes' : 'required', 'string', 'max:100'],
            'country' => [$this->req($sometimes), 'string', 'max:100'],
            'postal_code' => [$this->req($sometimes), 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    protected function pricingRules(bool $sometimes = false): array
    {
        return [
            // For create: one of price_per_night or price is required; for update: sometimes
            'price_per_night' => [$sometimes ? 'sometimes' : 'required_without:price', 'numeric', 'min:1'],
            'price' => [$sometimes ? 'sometimes' : 'required_without:price_per_night', 'numeric', 'min:1'],
            'price_per_week' => ['nullable', 'numeric', 'min:1'],
            'price_per_month' => ['nullable', 'numeric', 'min:1'],
            'cleaning_fee' => ['nullable', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function rulesRules(): array
    {
        return [
            'rules' => ['nullable', 'array'],
            'rules.*.title' => ['required', 'string'],
            'rules.*.description' => ['nullable', 'string'],
        ];
    }

    protected function availabilityRules(): array
    {
        return [
            'available_from' => ['nullable', 'date', 'after_or_equal:today'],
            'available_until' => ['nullable', 'date', 'after:available_from'],
            'blocked_dates' => ['nullable', 'array'],
            'blocked_dates.*' => ['date'],
            'custom_pricing' => ['nullable', 'array'],
        ];
    }

    protected function statusRules(bool $sometimes = false): array
    {
        return [
            'status' => [$sometimes ? 'sometimes' : 'nullable', 'in:available,booked,maintenance'],
            'is_active' => [$sometimes ? 'sometimes' : 'nullable', 'boolean'],
            'is_featured' => [$sometimes ? 'sometimes' : 'nullable', 'boolean'],
        ];
    }

    protected function amenitiesRules(): array
    {
        return [
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['exists:amenities,id'],
        ];
    }
}
