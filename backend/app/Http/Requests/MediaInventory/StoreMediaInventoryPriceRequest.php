<?php

namespace App\Http\Requests\MediaInventory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaInventoryPriceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Expects the route to bind the inventory model as {inventory}.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('managePrice', $this->route('inventory')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'base_price' => ['required', 'numeric', 'min:0'],
            'retail_percentage' => ['required', 'numeric', 'min:0', 'max:1000'],
            'b2c_percentage' => ['required', 'numeric', 'min:0', 'max:1000'],
            'b2b_percentage' => ['required', 'numeric', 'min:0', 'max:1000'],
            'enterprise_price' => ['nullable', 'numeric', 'gte:base_price'],
            'discount_type' => ['nullable', 'string', 'in:flat,percentage'],
            'discount_value' => ['nullable', 'required_with:discount_type', 'numeric', 'min:0'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'commission_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'platform_margin' => ['nullable', 'numeric', 'min:0'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];
    }
}
