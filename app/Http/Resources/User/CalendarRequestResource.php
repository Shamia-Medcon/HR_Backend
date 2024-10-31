<?php

namespace App\Http\Resources\User;

use App\Helper\_GlobalHelper;
use App\Http\Resources\BaseResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CalendarRequestResource extends BaseResource
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
            'title' => $this->User->first_name . " " . $this->User->last_name,
            'start' => date('Y-m-d', strtotime($leave_time[0])),
            'end' => date('Y-m-d 23:59:59', strtotime($leave_time[1])),
            'backgroundColor' => $this->LeaveType->is_reflectable ? (date('Y-m-d') < date('Y-m-d', strtotime($leave_time[1])) ? '#FF4C51cc' : 'grey') : '#16B1FF',
            'borderColor' => $this->LeaveType->is_reflectable ? (date('Y-m-d') < date('Y-m-d', strtotime($leave_time[1])) ? '#FF4C51cc' : 'grey') : '#16B1FF',
            'leave_time' => $this->leave_time,
            'numberOfDays' => _GlobalHelper::NumberOfDays($this->leave_time, $this->LeaveType->weekend_reflectable),
            'type' => $this->emergency ? "Emergency Leave" : $this->LeaveType->title,
            'user' => $this->User->first_name . " " . $this->User->last_name,
            'start_date' => $leave_time[0],
            'ticket_request' => $this->ticket_request,
            'end_date' => $leave_time[1],
            'manager' => [
                'value' => $this->User->Manager()->first() ? $this->User->Manager->first_name . " " . $this->User->Manager->last_name : "--",
                'message' => $this->User->Manager()->first() ? $this->User->Manager->id == Auth::guard('api')->user()->id ? "error" : "info" : "default"
            ],
            'status' => [
                'value' => $this['status'],
                'message' => $this['status'] == "pending" ? "warning" : ($this['status'] == "approved" ? "success" : "error")
            ],
            'days_available' => _GlobalHelper::GetTotalOfDays($this->User->id, $this->LeaveType->id),
            'created_at' => Carbon::parse($this->created_at)->format('d F, Y'),
        ];
    }
}
