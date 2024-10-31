<?php

namespace App\Services\Facades;

use App\Models\UserLeaveDay;
use App\Services\Interfaces\ITransfer;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FTransfer extends FBase implements ITransfer
{
    public function __construct()
    {
        $this->model = UserLeaveDay::class;
        $this->columns = [
            'note',
            'additional_days',
        ];
        $this->rules = [
            'note' => 'required',
            'additional_days' => 'required',
        ];
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        $request->validate($this->rules);
        $days = $request->input('additional_days');
        $year = Carbon::now()->addYear()->year;
        $check = $this->getByColumns([
            'user_id' => $user->id,
            'year' => $year
        ])->first();
        if ($check) {
            $newDays = $check->additional_days + $days;
            if ($newDays < 5) {
                $check->update([
                    'additional_days' => $newDays,
                    'status' => 0
                ]);
            } else {
                throw new Exception("The requested duration exceeds the permissible number of days.");
            }
        } else {
            $check = UserLeaveDay::query()->create([
                'note' => $request->note,
                'additional_days' => $request->additional_days,
                'year' => $year,
                'user_id' => $user->id,
                'status' => 0
            ]);
        }
        return $check;
    }

}
