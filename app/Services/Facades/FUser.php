<?php

namespace App\Services\Facades;

use App\Models\User;
use App\Services\Interfaces\IUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class FUser extends FBase implements IUser
{
    public function __construct()
    {
        $this->model = User::class;

        $this->columns = [
            'first_name',
            'last_name',
            'email',
            'password',
            'role',
            'manager_id',
        ];
        $this->rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'role' => 'required',

        ];
    }

    public function login(Request $request)
    {
        $user = $this->getByColumns([
            'email' => $request->input('email')
        ])->first();
        if ($user) {
            $check = Hash::check($request->input('password'), $user->password);
            if ($check) {
                return $user;
            }
        }
        return null;
    }
}
