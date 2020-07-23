<?php

namespace Talvbansal\TwillGlideDoSpaces;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class TwillGlideDoSpacesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/twill-glide-do-spaces.php' => config_path('twill-glide-do-spaces.php'),
            ], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/twill-glide-do-spaces.php', 'twill-glide-do-spaces');

        // if the glide sources are set to disk names lets fetch the flysystem driver for glide to use...
        if ($this->isDisk(env('GLIDE_SOURCE', ''))) {
            config()->set('twill.glide.source', Storage::disk(env('GLIDE_SOURCE', 'do_spaces'))->getDriver());
        }

        if ($this->isDisk(env('GLIDE_CACHE', ''))) {
            config()->set('twill.glide.cache', Storage::disk(env('GLIDE_CACHE', 'do_spaces'))->getDriver());
        }
    }

    private function isDisk(string $diskName = ''): bool
    {
        return array_key_exists($diskName, config('filesystems.disks'));
    }
}
