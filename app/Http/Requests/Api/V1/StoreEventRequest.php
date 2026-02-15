<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\Brand;
use App\Enums\EventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
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
        $minAttendees = $this->input('brand') === Brand::ByBike->value ? 30 : 1;

        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'brand' => ['required', Rule::enum(Brand::class)],
            'type' => ['required', Rule::enum(EventType::class)],
            'number_of_attendees' => ['required', 'integer', "min:{$minAttendees}"],
            'location' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'is_indoor' => ['required', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'The customer ID field is required.',
            'customer_id.exists' => 'The selected customer does not exist.',
            'brand.required' => 'The brand field is required.',
            'brand.enum' => 'The selected brand is invalid.',
            'type.required' => 'The type field is required.',
            'type.enum' => 'The selected type is invalid.',
            'number_of_attendees.required' => 'The number of attendees field is required.',
            'number_of_attendees.integer' => 'The number of attendees must be an integer.',
            'number_of_attendees.min' => 'The number of attendees must be at least :min.',
            'location.required' => 'The location field is required.',
            'date.required' => 'The date field is required.',
            'date.after_or_equal' => 'The date must be today or a future date.',
            'is_indoor.required' => 'The is indoor field is required.',
        ];
    }
}
