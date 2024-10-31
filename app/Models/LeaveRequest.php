<?php

namespace App\Models;

use App\Traits\Mediable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes, Mediable;

    protected $fillable = ['priority', 'leave_time', 'type_id', 'user_id',
        'manager_id', 'status', 'ticket_request',
        'note',
        'phone',
        'address',
        'inside_country',
        'country',
        'name_of_ken',
        'phone_of_ken',
        'ken_relation',
        'emergency'
    ];


    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function LeaveType()
    {
        return $this->belongsTo(LeaveType::class, 'type_id');
    }

}
