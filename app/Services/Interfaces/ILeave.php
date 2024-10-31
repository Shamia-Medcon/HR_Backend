<?php

namespace App\Services\Interfaces;

interface ILeave extends IBase
{

    public function changeStatus($status, $id);

}
