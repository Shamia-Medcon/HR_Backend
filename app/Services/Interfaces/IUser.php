<?php

namespace App\Services\Interfaces;

use Illuminate\Http\Request;

interface IUser extends IBase
{
    public function login(Request $request);
    public function sendresetlink(Request $request);
}
