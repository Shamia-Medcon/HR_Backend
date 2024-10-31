<?php

namespace App\Http\Resources\User;

use App\Http\Resources\BaseResource;
use Carbon\Carbon;

class DayTransferResource extends BaseResource
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
            'note' => $this->note,
            'status' => $this['status'] ? "Active" : "Not Active",
            '_status' => (bool)$this['status'],
            'year' => $this->year,
            'days' => $this->additional_days,
            'user' => $this->User->first_name . " " . $this->User->last_name,
            'created_at' => Carbon::parse($this->created_at)->format('d F, Y'),
        ];
    }
}
