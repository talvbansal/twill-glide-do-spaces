<?php

namespace Talvbansal\TwillGlideDoSpaces\Controllers;

use Talvbansal\TwillGlideDoSpaces\Services\MediaLibrary\Glide;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class ImageController
{
    public function show($path, Application $app)
    {
        return $app->make(Glide::class)->render($path);
    }
}
