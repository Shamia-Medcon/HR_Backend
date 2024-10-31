<?php

namespace App\Http\Controllers\User;

use App\Helper\_EmailHelper;
use App\Helper\_GlobalHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Http\Resources\User\CalendarRequestResource;
use App\Http\Resources\User\LeaveRequestResource;
use App\Models\LeaveType;
use App\Models\UserToken;
use App\Services\Interfaces\ILeave;
use App\Services\Interfaces\IUser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LeaveRequestController extends Controller
{

    private $leave, $email, $user;

    /**
     * @param $leave
     * @param $email
     */
    public function __construct(ILeave $leave, IUser $user)
    {
        $this->leave = $leave;
        $this->user = $user;
        $this->email = new _EmailHelper();
    }


    public function index(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();
            if ($user->role == "user") {
                $res = $this->leave->getByColumns([
                    'status' => 'pending',
                    'user_id' => $user->id,
                ])->get();
            } elseif ($user->role == "manager") {
                $res = $this->leave->getByColumns([
                    'status' => 'pending'
                ])->get();
            } else {
                $res = $this->leave->getByColumns([
                    'status' => 'pending'
                ])->get();
            }
            return LeaveRequestResource::paginable($res);
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function perUser(Request $request, $id)
    {
        try {
            $user = $this->user->getById($id);
            if ($user) {
                $res = $this->leave->getByColumns([
                    'user_id' => $user->id,
                ])->get();
                return LeaveRequestResource::paginable($res);
            }
            return BaseResource::return();
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function archive(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();
            if ($user->role == "user") {
                $res = $this->leave->getByColumns([
                    ['status', '!=', 'pending'],
                    'user_id' => $user->id,
                ])->get();
            } elseif ($user->role == "manager") {
                $res = $this->leave->getByColumns([
                    ['status', '!=', 'pending'],
                ])->get();
            } else {

                $res = $this->leave->getByColumns([
                    ['status', '!=', 'pending'],
                ])->get();
            }
            return LeaveRequestResource::paginable($res);
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function perWeek(Request $request)
    {
        try {
            $request->validate([
                'from' => 'required',
                'to' => 'required',
            ]);
            $user = Auth::guard('api')->user();
            $from = $request->input('from');
            $to = $request->input('to');
            if ($user->role == "user") {
                $res = $this->leave->getByColumns([
                    'user_id' => $user->id,
                    'status' => 'approved'
                ])->where(function ($query) use ($from, $to) {
                    $query->whereBetween(DB::raw("SUBSTRING_INDEX(leave_time, '/', 1)"), [$from, $to]);
                    $query->orWhereBetween(DB::raw("SUBSTRING_INDEX(leave_time, '/', -1)"), [$from, $to]);
                })->get();
            } else {
                $res = $this->leave->getByColumns([
                    'status' => 'approved'
                ])->where(function ($query) use ($from, $to) {
                    $query->whereBetween(DB::raw("SUBSTRING_INDEX(leave_time, '/', 1)"), [$from, $to]);
                    $query->orWhereBetween(DB::raw("SUBSTRING_INDEX(leave_time, '/', -1)"), [$from, $to]);
                })->get();
            }
            return CalendarRequestResource::collection($res);
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function getApproved(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if ($user->role == "user") {
                $res = $this->leave->getByColumns([
                    'user_id' => $user->id,
                    'status' => 'approved'
                ]);
            } elseif ($user->role == "manager") {
                $res = $this->leave->getByColumns([
                    'status' => 'approved'
                ]);
            } else {
                $res = $this->leave->getByColumns([
                    'status' => 'approved'
                ]);
            }
            if ($request->has('calendars')) {
                $list = [];
                foreach ($request->input('calendars') as $item) {
                    $type = LeaveType::query()->where([
                        'slug' => Str::slug($item)
                    ])->first();
                    if ($type) {
                        $list[] = $type->id;
                    }
                }
                $res = $res->whereIn('type_id', $list);

            }
            $res = $res->get();
            return CalendarRequestResource::paginable($res);
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function store(Request $request)
    {
        try {
            $res = $this->leave->store($request);
            if ($res) {
                return LeaveRequestResource::create($res);
            }
            return BaseResource::return();
        } catch (Exception $exception) {
            Log::error($exception);
            return BaseResource::return($exception->getMessage(), 201);
        }
    }

    public function changeStatus(Request $request, $id)
    {
        try {
            $rules = [
                'new-status' => 'required'
            ];
            $request->validate($rules);
            $leave = $this->leave->getById($id);
            $user = Auth::guard('api')->user();
            if ($user->role != "user") {
                if ($leave) {
                    $this->leave->changeStatus($request->input('new-status'), $id);
                    return BaseResource::ok();
                }
            }
            return BaseResource::return();
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }


    public function approveStatus(Request $request, $id)
    {
        try {
            $rule = [
                'token' => 'required'
            ];
            $request->validate($rule);
            $leave = $this->leave->getById($id);
            $token = UserToken::query()->where([
                'token' => $request->input('token')
            ])->first();
            if ($token) {
                $user = $token->User;
                if ($user->role != "user") {
                    if ($leave) {
                        $this->leave->changeStatus('approved', $id);
                        return redirect(getenv('_MAIN_URL'));
                    }
                }
            }

            return "Something wrong please try again";
        } catch (Exception $exception) {
            return "Something wrong please try again";
        }
    }

    public function denyStatus(Request $request, $id)
    {
        try {
            $rule = [
                'token' => 'required'
            ];
            $request->validate($rule);
            $leave = $this->leave->getById($id);
            $token = UserToken::query()->where([
                'token' => $request->input('token')
            ])->first();
            if ($token) {
                $user = $token->User;
                if ($user->role != "user") {
                    if ($leave) {
                        $this->leave->changeStatus('rejected', $id);
                        return redirect(getenv('_MAIN_URL'));
                    }
                }
            }
            return "Something wrong please try again";
        } catch (Exception $exception) {
            return "Something wrong please try again";
        }
    }

    public function show($id)
    {
        try {
            $res = $this->leave->getById($id);
            if ($res) {
                return LeaveRequestResource::create($res);
            }
            return BaseResource::return();

        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $res = $this->leave->update($request, $id);
            if ($res) {
                return LeaveRequestResource::create($res);
            }
            return BaseResource::return();

        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function destroy($id): JsonResponse|BaseResource
    {
        try {
            $res = $this->leave->delete($id);
            if ($res) {
                return BaseResource::ok();
            }
            return BaseResource::return();

        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

}
