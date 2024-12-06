<?php

namespace App\Http\Controllers\User;

use App\Helper\_EmailHelper;
use App\Helper\_GlobalHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Http\Resources\User\UserResource;
use App\Services\Interfaces\IType;
use App\Services\Interfaces\IUser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    private $user, $type;

    /**
     * @param IUser $user
     * @param IType $type
     */
    public function __construct(IUser $user, IType $type)
    {
        $this->user = $user;
        $this->type = $type;
    }


    public function index(Request $request)
    {
        try {
            $res = $this->user->getByColumns([
                ['role','<>', 'admin'],
            ])->get();
            return UserResource::paginable($res);
        } catch (Exception $exception) {
            return BaseResource::return();
        }
    }


    public function getManagers(Request $request)
    {
        try {
            $res = $this->user->getByColumns([
                'role' => 'manager',
            ])->get();
            return UserResource::collection($res);
        } catch (Exception $exception) {
            return BaseResource::return();
        }
    }

    public function resetPassword(Request $request)
{
    try {
        $email = $request->input('email');
        $user = $this->user->getByEmail($email);
        if ($user) {
            _EmailHelper::setPassword($user, []);
            return BaseResource::ok();
        }
        return BaseResource::return();
    } catch (Exception $exception) {
        Log::error($exception);
        return BaseResource::return();
    }
}

    public function preparePassword(Request $request, $token)
    {
        try {
            $check = _EmailHelper::checkToken($token);
            if ($check) {
                return view("password", [
                    'user' => $check->User
                ]);
            } else {
                return view("404");
            }
        } catch (Exception $exception) {
            return view("404");
        }
    }

    public function setPassword(Request $request, $id)
    {
        try {
            $rules = [
                'password' => 'required|confirmed|min:5',
            ];
            $request->validate($rules);
            $check = $this->user->getByColumns(['id' => $id])->update([
                'password' => Hash::make($request->input('password'))
            ]);
            if ($check) {
                return redirect(getenv('_MAIN_URL'));
            }
            return redirect()->back()->with($request->all());
        } catch (Exception $exception) {
            return redirect()->back()
                ->withInput(request()->except('_token'))
                ->withErrors([
                    'msg' => $exception->getMessage()
                ]);
        }
    }

    /**
     * User Info.
     *
     * @param Request $request
     * @return UserResource|JsonResponse
     */
    public function profile(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();
            if ($user) {
                return UserResource::create($user);
            } else {
                return BaseResource::return();
            }
        } catch (Exception $exception) {
            return BaseResource::return();
        }
    }

    public function updatePassword()
    {
        try {
            $this->user->getByColumns([])->update([
                'password' => Hash::make(12345)
            ]);
            return BaseResource::ok();
        } catch (e) {
            return BaseResource::return();
        }
    }


    public function login(Request $request)
    {
        try {
            $rules = [
                'email' => 'required',
                'password' => 'required',
            ];
            $request->validate($rules);
            $user = $this->user->login($request);
            if ($user) {
                return UserResource::create($user);
            }
            return BaseResource::return();
        } catch (Exception $exception) {
            Log::error($exception);
            return BaseResource::exception($exception);
        }
    }


    public function remainingDays(Request $request)
    {
        try {
            $rule = [
                'type_id' => 'required',
            ];
            $request->validate($rule);
            $user = Auth::guard('api')->user();
            if ($user) {
                $type = $this->type->getById($request->input('type_id'));
                if ($type) {
                    $days = _GlobalHelper::GetTotalOfDays($user->id, $request->input('type_id'));
                    return BaseResource::create([
                        'days' => $days,
                        'max_days' => $type->max_days
                    ]);
                }
            }
            return BaseResource::return();
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }

    }

//    public function

    public function store(Request $request)
    {
        try {
            $user = $this->user->store($request);
            if ($user) {
                _EmailHelper::setPassword($user, []);
                return UserResource::create($user);
            }
            return BaseResource::return();
        } catch (Exception $exception) {
            Log::error($exception);
            return BaseResource::return();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = $this->user->update($request, $id);
            if ($user) {
                return UserResource::create($user);
            }
            return BaseResource::return();
        } catch
        (Exception $exception) {
            Log::error($exception->getMessage());
            return BaseResource::return();
        }
    }

    public function show($id)
    {
        try {

            $user = $this->user->getById($id);
            if ($user) {
                return UserResource::create($user);
            }
            return BaseResource::return();
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return BaseResource::return();
        }
    }


}
