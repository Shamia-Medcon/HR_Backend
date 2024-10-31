<?php

namespace App\Helper;

use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Intervention\Image\Gd\Driver;
use Intervention\Image\ImageManager;

class _MediaHelper
{

    private $media;

    /**
     */
    public function __construct()
    {

    }


    public function upload($file, $public_ID)
    {

        $extension = $file->getClientOriginalExtension();
        $fileNameToStore = $public_ID . "." . $extension;
        $file->storeAs('public/attachments', $fileNameToStore);
    }

    public function uploadVideo($file, $public_ID)
    {

    }

    public static function getURL($publicID, $type, $width = null): string
    {
        return "";
    }

    public function delete($url, $type)
    {
        return null;
    }


}
