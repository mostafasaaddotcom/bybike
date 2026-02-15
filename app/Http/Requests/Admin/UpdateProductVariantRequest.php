<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductVariantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
            'minimum_order_quantity' => ['required', 'integer', 'min:1'],
            'increase_rate' => ['required', 'integer', 'min:1'],
            'maximum_available_quantity_per_day' => ['nullable', 'integer', 'min:1'],
            'is_available' => ['required', 'boolean'],
            'price_tiers' => ['nullable', 'array'],
            'price_tiers.*.quantity_from' => ['required', 'integer', 'min:1'],
            'price_tiers.*.quantity_to' => ['nullable', 'integer', 'min:1'],
            'price_tiers.*.price' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Please select a product.',
            'name.required' => 'The variant name is required.',
            'minimum_order_quantity.required' => 'Minimum order quantity is required.',
            'minimum_order_quantity.min' => 'Minimum order quantity must be at least 1.',
            'increase_rate.required' => 'Increase rate is required.',
            'increase_rate.min' => 'Increase rate must be at least 1.',
            'price_tiers.*.quantity_from.required' => 'Quantity from is required for each price tier.',
            'price_tiers.*.price.required' => 'Price is required for each price tier.',
            'price_tiers.*.price.min' => 'Price must be at least 0.',
        ];
    }
}
