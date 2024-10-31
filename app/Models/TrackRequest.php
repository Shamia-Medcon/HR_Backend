<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'status', 'leave_id', 'user_id', 'type_id'
    ];

    public function LeaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_id');
    }
    public function LeaveType()
    {
        return $this->belongsTo(LeaveType::class, 'type_id');
    }
    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
