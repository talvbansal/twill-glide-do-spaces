<?php

namespace Talvbansal\TwillGlideDoSpaces\Controllers;

use Illuminate\Foundation\Application;
use Talvbansal\TwillGlideDoSpaces\Services\MediaLibrary\Glide;

class ImageController
{
    public function show($path, Application $app)
    {
        return $app->make(Glide::class)->render($path);
    }
}
