<?php

namespace App\Models;

use App\Traits\Mediable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LeaveBalance extends Model
{
    use HasFactory, SoftDeletes, Mediable;

    protected $fillable = [
        'user_id', 'annual_leave', 'sick_leave', 'work_form_home'

    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->slug = Str::slug($model->title);
        });
    }
}
