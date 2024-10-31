<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    use HasFactory;
    protected $fillable=[
        'source','target','leave_request_id'
    ];
    public function LeaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }

}
