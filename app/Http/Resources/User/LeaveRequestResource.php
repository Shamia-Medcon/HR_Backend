<?php

namespace App\Http\Resources\User;

use App\Helper\_GlobalHelper;
use App\Http\Resources\BaseResource;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\UserLeaveDay;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeaveRequestResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $leave_time = explode("/", $this->leave_time);
        return [
            'id' => $this->id,
            'priority' => (bool)$this->priority,
            'leave_time' => $this->leave_time,
            'numberOfDays' => _GlobalHelper::NumberOfDays($this->leave_time, $this->LeaveType->weekend_reflectable),
            'type' => $this->emergency ? "Emergency Leave" : $this->LeaveType->title,
            'user' => $this->User->first_name . " " . $this->User->last_name,
            'start_date' => $leave_time[0],
            'ticket_request' => $this->ticket_request,
            'ask_for_ticket' => $this->LeaveType->ticket_request ? ($this->ticket_request ? "Request a ticket" : "-") : "-",
            'end_date' => $leave_time[1],
            'manager' => [
                'value' => $this->Manager()->first() ? $this->Manager->first_name . " " . $this->Manager->last_name : "--",
                'message' => "default"
            ],
            'status' => [
                'value' => $this['status'],
                'message' => $this['status'] == "pending" ? "warning" : ($this['status'] == "approved" ? "success" : "error")
            ],
            'days_available' => ($this['status'] == "pending" ? _GlobalHelper::GetTotalOfDays($this->User->id, $this->LeaveType->id, $this->id) : '-'),
            'created_at' => Carbon::parse($this->created_at)->format('d F, Y'),
        ];
    }


}
