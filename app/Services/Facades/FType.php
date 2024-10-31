<?php

namespace App\Services\Facades;

use App\Models\LeaveType;
use App\Services\Interfaces\IType;

class FType extends FBase implements IType
{

    public function __construct()
    {
        $this->model = LeaveType::class;

        $this->columns = [
            'title',
            'subtitle',
            'is_reflectable',
            'is_attached',
            'ticket_request',
            'additional_days_available',
            'days',
            'salary_reflectable',
            'ken_required',
            'contact_required',
            'address_required',
            'weekend_reflectable',
            'per',
            'days_for_attachment',
            'before_days',
            'max_days',
            'emergency'
        ];
        $this->rules = [
            'title' => 'required',
            'days' => 'required',
            'per' => 'required',
            'before_days' => 'required',
        ];
    }
}
