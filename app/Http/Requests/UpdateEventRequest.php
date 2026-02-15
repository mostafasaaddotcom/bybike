<?php

namespace App\Http\Requests;

use App\Enums\Brand;
use App\Enums\EventStatus;
use App\Enums\EventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'is_indoor' => ['required', 'boolean'],
            'status' => ['required', Rule::enum(EventStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
