<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\PropertyRuleSets;

class UpdatePropertyRequest extends FormRequest
{
    use PropertyRuleSets;
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
        return array_merge(
            $this->baseInfoRules(true),
            $this->detailsRules(true),
            $this->addressRules(true),
            $this->pricingRules(true),
            $this->rulesRules(),
            $this->availabilityRules(),
            $this->statusRules(true),
            $this->amenitiesRules(),
        );
    }
}
