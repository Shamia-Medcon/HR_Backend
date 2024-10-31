<?php

namespace App\Console\Commands;

use App\Helper\_GlobalHelper;
use App\Models\LeaveType;
use App\Models\User;
use App\Models\UserLeaveDay;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EndOfYearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:end-of-year';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute function at the end of the year';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::query()->where([
            ['role', '<>', 'admin']
        ])->get();
        $type = LeaveType::query()->where(['additional_days_available' => true])->first();
        if ($type) {
            foreach ($users as $user) {
                $days = _GlobalHelper::GetTotalOfDays($user->id, $type->id);
                $shiftDays = min($days, 5);
                if ($shiftDays >= 1) {
                    $year = Carbon::now()->addYear()->year;
                    $check = UserLeaveDay::query()->where([
                        'user_id' => $user->id,
                        'year' => $year,
                        'status' => true
                    ])->first();
                    if (!$check) {
                        UserLeaveDay::query()->create([
                            'user_id' => $user->id,
                            'year' => $year,
                            'status' => true,
                            'additional_days' => $shiftDays,
                            'note' => "Shift days from " . date('Y')
                        ]);
                    }
                }

            }
        }

    }
}
