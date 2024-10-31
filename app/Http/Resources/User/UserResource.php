<?php

namespace App\Http\Resources\User;

use App\Helper\_GlobalHelper;
use App\Http\Resources\BaseResource;
use App\Models\LeaveType;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use JsonSerializable;

class UserResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $manager = $this->Manager;
        return [
            'id' => $this->id,
            'fullName' => $this->first_name . " " . $this->last_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'manager' => $manager ? $manager->first_name . " " . $manager->last_name : "-",
            'manager_details' => $this->getManager($manager),
            'days' => $this->getDays($this),
            'consumed_days' => $this->getConsumeDays($this),
            'role' => $this->role,
            'token' => $this->createToken('API Token')->accessToken,
        ];
    }

    public function getDays($user)
    {
        $types = LeaveType::query()->get();
        $res = [];
        try {
            foreach ($types as $type) {
                $res[] = [
                    'type' => $type->title,
                    'key' => $type->title . ": " . _GlobalHelper::GetTotalOfDays($user->id, $type->id),
                    'days' => _GlobalHelper::GetTotalOfDays($user->id, $type->id)
                ];
            }
            return $res;
        } catch (Exception $exception) {
            return [];
        }
    }

    public function getConsumeDays($user)
    {
        $types = LeaveType::query()->get();
        $res = [];
        try {
            foreach ($types as $type) {
                $res[] = [
                    'type' => $type->title,
                    'key' => $type->title . ": " . _GlobalHelper::GetConsumedDays($user->id, $type->id),
                    'days' => _GlobalHelper::GetConsumedDays($user->id, $type->id)
                ];
            }
            return $res;
        } catch (Exception $exception) {
            Log::error($exception);
            return [];
        }
    }

    public function getManager($user)
    {
        if ($user) {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ];
        }
        return null;
    }
}
