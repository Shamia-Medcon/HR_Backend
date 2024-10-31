<?php


namespace App\Traits;


use App\Models\Media;

trait Mediable
{

    public function images()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function files()
    {
        return $this->morphMany(Media::class, 'mediable');
    }



}
