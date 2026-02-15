<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'brand' => $this->brand,
            'type' => $this->type,
            'number_of_attendees' => $this->number_of_attendees,
            'location' => $this->location,
            'date' => $this->date,
            'is_indoor' => $this->is_indoor,
            'status' => $this->status,
            'notes' => $this->notes,
            'public_invoice_url' => $this->public_invoice_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
