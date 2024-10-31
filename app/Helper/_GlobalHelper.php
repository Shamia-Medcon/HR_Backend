<?php

namespace App\Helper;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\UserLeaveDay;
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

    public static function GetTotalOfDays($user_id, $type_id, $max_id = null)
    {
        try {
            $type = LeaveType::query()->where('id', $type_id)->first();
            if (!$type) {
                throw new Exception("Leave type is not correct");
            }
            $days_over = self::GetDays($user_id, $type, $max_id);
            $days = $type->days;

            if ($type->additional_days_available) {
                $additional_days = UserLeaveDay::query()->where([
                    'user_id' => $user_id,
                    'year' => date('Y'),
                    'status' => true
                ])->first();
                if ($additional_days) {
                    $days = $days + $additional_days->additional_days;
                }
            }
            foreach ($days_over as $item) {
                $days = $days - _GlobalHelper::NumberOfDays($item->leave_time, $type->weekend_reflectable);
            }
            return $days;
        } catch (Exception $exception) {
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
