<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserToken extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['token', 'user_id'];

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
