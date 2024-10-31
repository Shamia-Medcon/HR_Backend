<?php

namespace App\Http\Resources\User;

use App\Http\Resources\BaseResource;

class LeaveTypeResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'days' => $this->days,
            'is_reflectable' => (bool)$this->is_reflectable,
            'is_attached' => (bool)$this->is_attached,
            'ticket_request' => (bool)$this->ticket_request,
            'salary_reflectable' => (bool)$this->salary_reflectable,
            'ken_required' => (bool)$this->ken_required,
            'contact_required' => (bool)$this->contact_required,
            'address_required' => (bool)$this->address_required,
            'weekend_reflectable' => (bool)$this->weekend_reflectable,
            'reflectable' => $this->is_reflectable ? "Reflectable" : "-",
            'attached' => $this->is_attached ? "Attachment is required" : "-",
            'ticket_available' => $this->ticket_request ? "Ticket Available" : "-",
            'per' => $this->per,
            'emergency' => (bool)$this->emergency,
            'before_days' => $this->before_days,
            'max_days' => $this->max_days,
            'days_for_attachment' => $this->days_for_attachment
        ];
    }
}
