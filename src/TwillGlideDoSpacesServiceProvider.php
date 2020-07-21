<?php

namespace Talvbansal\TwillGlideDoSpaces;

use A17\Twill\Repositories\FileRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Talvbansal\TwillGlideDoSpaces\Controllers\ImageController;

class TwillGlideDoSpacesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/twill-glide-do-spaces.php' => config_path('twill-glide-do-spaces.php'),
            ], 'config');
        }

        // image routes...
        Route::get('/' . config('twill.glide.base_path') . '/{path}', [ImageController::class, 'show'])->where('path', '.*');
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

        // Twill creates 2 new disks on the fly, here we set a new root for files uploaded by the media and file uploaders...
        config()->set([
            'filesystems.disks.twill_file_library.root' => env('DO_SPACES_FILES_ROOT', 'files/'),
            'filesystems.disks.twill_media_library.root' => env('DO_SPACES_IMAGES_ROOT', 'img/'),
        ]);

        // Overwrite Twill's File Repo with our version until this is patched...
        $this->app->bind(FileRepository::class, \Talvbansal\TwillGlideDoSpaces\Repositories\FileRepository::class);
    }

    private function isDisk(string $diskName = ''): bool
    {
        return array_key_exists($diskName, config('filesystems.disks'));
    }
}
