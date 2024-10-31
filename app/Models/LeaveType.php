<?php

namespace App\Models;

use App\Traits\Mediable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LeaveType extends Model
{
    use HasFactory, SoftDeletes, Mediable;

    protected $fillable = [
        'title', 'slug', 'subtitle', 'is_reflectable', 'is_attached', 'ticket_request', 'additional_days_available', 'days',
        'salary_reflectable',
        'ken_required',
        'contact_required',
        'address_required',
        'weekend_reflectable',
        'days_for_attachment',
        'per',
        'before_days',
        'max_days',
        'emergency'

    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->slug = Str::slug($model->title);
        });
    }
}
