<?php

namespace App\Helper;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\UserLeaveDay;
use App\Models\LeaveBalance;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class _GlobalHelper
{
    public static function NumberOfDays($date, $weekend_reflectable)
    {
        [$startDate, $endDate] = explode('/', $date);
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        if ($weekend_reflectable) {
            $diff = $startDate->diff($endDate->addDay());
            return $diff->format('%a');
        } else {
            return $startDate->diffInDaysFiltered(function (Carbon $date) {
                return $date->isWeekday();
            }, $endDate->addDay());
        }
    }

    public static function GetDays($user_id, $type, $max_id = null)
    {
        try {

            $checkOldRequest = LeaveRequest::query()->where([
                'status' => 'approved',
                'user_id' => $user_id,
                'type_id' => $type->id
            ]);
            Log::info('old request...', ['result' => $checkOldRequest]);

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
                    $currentEnd = Carbon::now()->endOfYear(); // Get the end of the current week

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


            return $checkOldRequest->get();

        } catch (Exception $exception) {
            return [];
        }
    }

    // public static function GetTotalOfDays($user_id, $type_id, $max_id = null)
    // {
    //     try {
    //         $type = LeaveType::query()->where('id', $type_id)->first();
    //         if (!$type) {
    //             throw new Exception("Leave type is not correct");
    //         }
    //         $days_over = self::GetDays($user_id, $type, $max_id);
    //         $days = $type->days;

    //         if ($type->additional_days_available) {
    //             $additional_days = UserLeaveDay::query()->where([
    //                 'user_id' => $user_id,
    //                 'year' => date('Y'),
    //                 'status' => true
    //             ])->first();
    //             if ($additional_days) {
    //                 $days = $days + $additional_days->additional_days;
    //             }
    //         }
    //         foreach ($days_over as $item) {
    //             $days = $days - _GlobalHelper::NumberOfDays($item->leave_time, $type->weekend_reflectable);
    //         }
    //         return $days;
    //     } catch (Exception $exception) {
    //         return 0;
    //     }
    // }

    public static function GetTotalOfDays($user_id, $type_id, $max_id = null)
{
    try {
        $type = LeaveType::query()->where('id', $type_id)->first();
        if (!$type) {
            throw new Exception("Leave type is not correct");
        }
        Log::info('Leave Type: ', ['type' => $type]);
        // Retrieve the leave balance directly from the LeaveBalance table instead of calling GetDays
        $leaveBalance = LeaveBalance::query()->where('user_id', $user_id)->first();
        if (!$leaveBalance) {
            throw new Exception("Leave balance record not found for the user");
        }
        Log::info('Leave Balance: ', ['leave_balance' => $leaveBalance]);
        // Set the initial days based on leave type balance from LeaveBalance
        $days = 0;
        switch ($type->slug) {
            case 'annual-leave':
                $days = $leaveBalance->annual_leave;
                break;
            case 'sick-leave':
                $days = $leaveBalance->sick_leave;
                break;
            case 'working-from-home':
                $days = $leaveBalance->work_from_home;
                break;
            default:
                throw new Exception("Invalid leave type specified");
        }
        Log::info('Days After Assigning Balance: ', ['days' => $days]);
        // Add any additional days if applicable
        if ($type->additional_days_available) {
            $additional_days = UserLeaveDay::query()->where([
                'user_id' => $user_id,
                'year' => date('Y'),
                'status' => true
            ])->first();
            if ($additional_days) {
                $days += $additional_days->additional_days;
                Log::info('Additional Days Added: ', ['additional_days' => $additional_days->additional_days]);
            }
        }
        Log::info('Final Days: ', ['final_days' => $days]);
        // No loop required since days_over is no longer being calculated
        return $days;
    } catch (Exception $exception) {
        Log::error('Error in GetTotalOfDays: ', ['error' => $exception->getMessage()]);
        return 0;
    }
}

    public static function GetConsumedDays($user_id, $type_id, $max_id = null)
    {
        try {
            $type = LeaveType::query()->where('id', $type_id)->first();
            if (!$type) {
                throw new Exception("Leave type is not correct");
            }
            $days_over = self::GetDays($user_id, $type, $max_id);
            $days = 0;
            foreach ($days_over as $item) {
                $days = $days + _GlobalHelper::NumberOfDays($item->leave_time, $type->weekend_reflectable);
            }
            return $days;
        } catch (Exception $exception) {
            return 0;
        }
    }
}
