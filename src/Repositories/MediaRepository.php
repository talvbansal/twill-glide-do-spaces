<?php

namespace Talvbansal\TwillGlideDoSpaces\Repositories;

use A17\Twill\Models\Media;
use A17\Twill\Repositories\Behaviors\HandleTags;
use A17\Twill\Repositories\ModuleRepository;
use A17\Twill\Services\MediaLibrary\ImageService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class MediaRepository extends ModuleRepository
{
    use HandleTags;

    /**
     * @param Media $model
     */
    public function __construct(Media $model)
    {
        $this->model = $model;
    }

    public function filter($query, array $scopes = [])
    {
        $this->searchIn($query, $scopes, 'search', ['alt_text', 'filename', 'caption']);

        return parent::filter($query, $scopes);
    }

    public function afterDelete($object)
    {
        $storageId = $object->uuid;
        if (Config::get('twill.media_library.cascade_delete')) {
            // if an s3 disk has a root other than / set it'll be re-applied when calculating the file path so we need to strip it here...
            $diskName = config('twill.media_library.disk');
            $diskConfig = config('filesystems.disks.'.$diskName);
            $diskRoot = $diskConfig['root'] ?? false;

            if ($diskConfig['driver'] === 's3' && $diskRoot) {
                $storageId = ltrim($storageId, $diskRoot);
            }
            Storage::disk(Config::get('twill.media_library.disk'))->delete($storageId);
        }
    }

    public function prepareFieldsBeforeCreate($fields)
    {
        if (Config::get('twill.media_library.init_alt_text_from_filename', true)) {
            $fields['alt_text'] = $this->model->altTextFrom($fields['filename']);
        }

        // if we were not able to determine dimensions with the browser File API, let's ask the Image service
        if (! isset($fields['width']) || ! isset($fields['height'])) {
            $dimensions = ImageService::getDimensions($fields['uuid']);
            $fields['width'] = $dimensions['width'] ?? 0;
            $fields['height'] = $dimensions['height'] ?? 0;
        }

        return $fields;
    }
}
