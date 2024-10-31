<?php

namespace App\Services\Facades;

use App\Helper\_EmailHelper;
use App\Helper\_GlobalHelper;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\TrackRequest;
use App\Services\Interfaces\ILeave;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FLeave extends FBase implements ILeave
{
    public function __construct()
    {
        $this->model = LeaveRequest::class;


        $this->columns = [
            'priority',
            'leave_time',
            'type_id',
            'status',
            'user_id',
            'manager_id',
            'ticket_request',
            'status',
            'note',
            'phone',
            'address',
            'inside_country',
            'country',
            'name_of_ken',
            'phone_of_ken',
            'ken_relation',
            'emergency'

        ];
        $this->rules = [
            'leave_time' => 'required',
            'type_id' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'inside_country' => 'required',
            'country' => 'required',
            'name_of_ken' => 'required',
            'phone_of_ken' => 'required',
            'ken_relation' => 'required',

        ];
    }

    public function store(Request $request)
    {

        DB::beginTransaction();
        $rules = [
            'leave_time' => 'required',
            'type_id' => 'required',
//            'note' => 'required',
        ];


        $type = LeaveType::query()->where([
            'id' => $request->input('type_id')
        ])->first();
        if (!$type) {
            throw new Exception("Type is not exist");
        }
        $user = Auth::guard('api')->user();
        $manager = $user->Manager()->first();
        $currentYear = date('Y');
        $checkOldRequest = LeaveRequest::query()->where([
            'user_id' => $user->id,
            'status' => 'approved'
        ]);
        if ($type->ticket_request) {
            $checkOldRequest = $checkOldRequest->where([
                'ticket_request' => true,
            ]);
        }
        $currentStart = Carbon::now(); // Get the start of the current week
        $currentEnd = Carbon::now(); // Get the end of the current week

        switch ($type->per) {
            case "week":
                $currentStart = Carbon::now()->startOfWeek(); // Get the start of the current week
                $currentEnd = Carbon::now()->endOfWeek(); // Get the end of the current week

                $checkOldRequest = $checkOldRequest->where(function ($query) use ($currentStart, $currentEnd) {
                    $query->whereBetween(DB::raw('SUBSTRING_INDEX(leave_time, "/", 1)'), [$currentStart, $currentEnd])
                        ->orWhereBetween(DB::raw('SUBSTRING_INDEX(leave_time, "/", -1)'), [$currentStart, $currentEnd]);
                });
                break;
            case "year":
                $currentStart = Carbon::now()->startOfYear(); // Get the start of the current week
                $currentEnd = Carbon::now()->startOfYear(); // Get the end of the current week

                $checkOldRequest = $checkOldRequest->where(function ($query) use ($currentStart, $currentEnd) {
                    $query->whereBetween(DB::raw('SUBSTRING_INDEX(leave_time, "/", 1)'), [$currentStart, $currentEnd])
                        ->orWhereBetween(DB::raw('SUBSTRING_INDEX(leave_time, "/", -1)'), [$currentStart, $currentEnd]);
                });
                break;
            case "month":
                $currentStart = Carbon::now()->startOfMonth(); // Get the start of the current week
                $currentEnd = Carbon::now()->endOfMonth(); // Get the end of the current week
                $checkOldRequest = $checkOldRequest->where(function ($query) use ($currentStart, $currentEnd) {
                    $query->whereBetween(DB::raw('SUBSTRING_INDEX(leave_time, "/", 1)'), [$currentStart, $currentEnd])
                        ->orWhereBetween(DB::raw('SUBSTRING_INDEX(leave_time, "/", -1)'), [$currentStart, $currentEnd]);
                });
                break;
            default:
                break;
        }
        $checkOldRequest = $checkOldRequest->first();
        $currentStart = Carbon::parse($currentStart)->format('Y-m-d');
        $currentEnd = Carbon::parse($currentEnd)->format('Y-m-d');
        if ($checkOldRequest && $type->ticket_request && $request->input('ticket_request') == 1) {
            throw new Exception("Our records indicate you have already submitted a ticket request for the period: " . ($currentStart . "/" . $currentEnd), 201);
        } else if ($checkOldRequest) {
            throw new Exception("According to our records, it appears that you have already requested (" . $type->title . ") for the period between: " . ($currentStart . " and " . $currentEnd), 201);
        }
        $object = [
            'type_id' => $type->id,
            'user_id' => $user->id,
            'manager_id' => $manager ? $manager->id : null,
            'leave_time' => $request->input('leave_time'),
            'note' => $request->input('note'),
            'ticket_request' => $request->has('ticket_request') && $request->input('ticket_request') == 1 && $request->input('inside_country') == 0 ? 1 : 0,
            'status' => 'pending'
        ];
        if ($type->emergency && $request->has('emergency')) {
            $object['emergency'] = $request->input('emergency');
        }
        if ($type->ken_required) {
            $rules["name_of_ken"] = 'required';
            $rules["phone_of_ken"] = 'required';
            $rules["ken_relation"] = 'required';
            $object['name_of_ken'] = $request->input('name_of_ken');
            $object['ken_relation'] = $request->input('ken_relation');
            $object['phone_of_ken'] = $request->input('phone_of_ken');
        }
        if ($type->address_required) {
            $rules["inside_country"] = 'required';
            $object['inside_country'] = $request->input('inside_country');
            $rules["address"] = 'required';
            if ($request->input('inside_country') == 0) {
                $rules["country"] = 'required';
                $object['country'] = $request->input('country');
            }
            $object['address'] = $request->input('address');
        }
        if ($type->contact_required) {
            $rules["phone"] = 'required';
            $object['phone'] = $request->input('phone');
        }
        $numberOfDays = _GlobalHelper::NumberOfDays($request->input('leave_time'), $type->weekend_reflectable);
        if ($type->is_attached && $numberOfDays >= $type->days_for_attachment) {
            $rules["attachment"] = 'required';
        }
        $request->validate($rules);
        $leave = LeaveRequest::query()->create($object);
        if ($leave) {
            if ($request->hasFile('attachment')) {
                $this->uploadFile($leave, $request->allFiles());
            }


            $this->track($leave);
            if ($manager) {
                $this->sendEmail($leave, $manager);
            }
        }
        DB::commit();

        return $leave;
    }

    public function update(Request $request, $id)
    {
        $leave = $this->getById($id);
        if (!$leave) {
            throw new Exception("Record is not exist");
        }
        $request->validate($this->rules);
        $type = LeaveType::query()->where([
            'id' => $request->input('type_id')
        ])->first();
        if (!$type) {
            throw new Exception("Type is not exist");
        }
        $leave->update([
            'type_id' => $type->id,
            'leave_time' => $request->input('leave_time'),
        ]);

        $user = Auth::guard('api')->user();
        $manager = $user->Manager()->first();
        $this->track($leave);
        if ($manager) {
            $this->sendEmail($leave, $manager);
        }
        return $leave;
    }

    public function changeStatus($status, $id)
    {
        $leave = $this->getById($id);
        if (!$leave) {
            throw new Exception("Record is not exist");
        }
        $leave->update([
            'status' => $status
        ]);
        $user = $leave->User()->first();
        $manager = Auth::guard('api')->user();
        $this->track($leave);
        $this->sendStatus($leave, $user, $manager);
        return $leave;
    }

    public function track($leave)
    {
        TrackRequest::query()->create([
            'status' => $leave->status,
            'leave_id' => $leave->id,
            'user_id' => $leave->user_id,
            'type_id' => $leave->type_id,
        ]);
    }

    public function sendEmail($leave, $manager)
    {

        $leave_time = explode('/', $leave->leave_time);
        $files = $leave->files()->get();
        $attachments = [];
        $token = _EmailHelper::generateToken($leave->User->Manager);
        foreach ($files as $file) {
            $url = public_path('storage/attachments/' . $file->url); // Path to your file in the storage folder
            $attachments[] = $url;
        }
        _EmailHelper::sendNotification($manager, [
            'id' => $leave->id,
            'name' => $leave->User->first_name . " " . $leave->User->last_name,
            'status' => $leave->status,
            'leave_type' => $leave->LeaveType->title,
            'leave_time' => $leave->leave_time,
            'phone' => $leave->phone ?? null,
            'address' => $leave->address ?? null,
            'ken_relation' => $leave->ken_relation ?? null,
            'phone_of_ken' => $leave->phone_of_ken ?? null,
            'name_of_ken' => $leave->name_of_ken ?? null,
            'country' => $leave->inside_country == 1 ? "UAE" : $leave->country,
            'inside_country' => $leave->inside_country,
            'ticket_request' => $leave->LeaveType->ticket_request ? ($leave->ticket_request ? "A request was made for a ticket." : "No request was made for a ticket.") : null,
            'start_date' => count($leave_time) == 2 ? $leave_time[0] : "--",
            'end_date' => count($leave_time) == 2 ? $leave_time[1] : "--",
            'notes' => $leave->note ?? null,
            'manager' => $leave->User->Manager->first_name . " " . $leave->User->Manager->last_name,
            'numberOfDays' => _GlobalHelper::NumberOfDays($leave->leave_time, $leave->LeaveType->weekend_reflectable),
            'token' => $token

        ], $attachments);

    }

    public function sendStatus($leave, $user, $manager)
    {
        $leave_time = explode('/', $leave->leave_time);
        _EmailHelper::sendNotificationToUser($user, [
            'id' => $leave->id,
            'name' => $leave->User->first_name . " " . $leave->User->last_name,
            'status' => $leave->status,
            'leave_type' => $leave->LeaveType->title,
            'leave_time' => $leave->leave_time,
            'phone' => $leave->phone,
            'address' => $leave->address,
            'ken_relation' => $leave->ken_relation,
            'phone_of_ken' => $leave->phone_of_ken,
            'name_of_ken' => $leave->name_of_ken,
            'country' => $leave->inside_country == 1 ? "UAE" : $leave->country,
            'inside_country' => $leave->inside_country,
            'ticket_request' => $leave->ticket_request ? "A request was made for a ticket." : "No request was made for a ticket.",
            'start_date' => count($leave_time) == 2 ? $leave_time[0] : "--",
            'end_date' => count($leave_time) == 2 ? $leave_time[1] : "--",
            'notes' => $leave->note,
            'manager' => $leave->User->Manager->first_name . " " . $leave->User->Manager->last_name,
            'numberOfDays' => _GlobalHelper::NumberOfDays($leave->leave_time, $leave->LeaveType->weekend_reflectable),
        ]);
    }
}
