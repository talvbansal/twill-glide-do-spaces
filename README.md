# Make twill work with glide and digital ocean spaces easily.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/talvbansal/twill-glide-do-spaces.svg?style=flat-square)](https://packagist.org/packages/talvbansal/twill-glide-do-spaces)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/talvbansal/twill-glide-do-spaces/run-tests?label=tests)](https://github.com/talvbansal/twill-glide-do-spaces/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/talvbansal/twill-glide-do-spaces.svg?style=flat-square)](https://packagist.org/packages/talvbansal/twill-glide-do-spaces)



## Installation

You can install the package via composer:

```bash
composer require talvbansal/twill-glide-do-spaces
```

## Usage

This package overwrites some of twill's functionality to provide a working set of code that allows digital ocean spaces to store assets and work with glide.
Whilst Digital Ocean has an S3 compatible api twill's uses a mix of it and a custom implementation.

The glide config can be pretty gnarly to work with so the documentation below should help get things going.

#### Environment

Digital Ocean Spaces uses an S3 compatible API. Start by creating a new disk in your `config/filesystem.php` for the digital ocean space.

```php
// config/filesystems.php
    ...
    'disks' => [
        ...
        'do_spaces' => [
            'use_https' => true,
            'driver' => 's3',
            'key' => env('DO_SPACES_KEY'),
            'secret' => env('DO_SPACES_SECRET'),
            'endpoint' => env('DO_SPACES_ENDPOINT'),
            'region' => env('DO_SPACES_REGION', 'AMS3'),
            'bucket' => env('DO_SPACES_BUCKET'),
        ],

    ],
    ...
```

Twill uses a custom implementation of the S3 storage provider. Create the variables for the disk above and then we'll re-use them for twill as much as possible.
```
// .env
DO_SPACES_KEY=xxxx
DO_SPACES_SECRET=yyyy
DO_SPACES_ENDPOINT=https://ams3.digitaloceanspaces.com
DO_SPACES_REGION=AMS3
DO_SPACES_BUCKET=bucketname

S3_KEY="${DO_SPACES_KEY}"
S3_SECRET="${DO_SPACES_SECRET}"
S3_ENDPOINT="${DO_SPACES_ENDPOINT}"
S3_REGION="${DO_SPACES_REGION}"
S3_BUCKET="${DO_SPACES_BUCKET}"

DO_SPACES_IMAGES_ROOT="img/"
DO_SPACES_FILES_ROOT="files/"

# the name of the storage disk...
GLIDE_CACHE=do_spaces
GLIDE_SOURCE=do_spaces
```

Let's also update twill to use this package's glide implementation...
```bash
// .env
MEDIA_LIBRARY_ENDPOINT_TYPE=s3
MEDIA_LIBRARY_ACL=public-read  # Optional
MEDIA_LIBRARY_IMAGE_SERVICE=Talvbansal\TwillGlideDoSpaces\Services\MediaLibrary\Glide
FILE_LIBRARY_ENDPOINT_TYPE=s3
```

Finally make sure these entries exist in the `config/twill.php`:

```php
// config/twill.php
return [
    'glide' => [
        'source' => env('GLIDE_SOURCE', storage_path('app/public/uploads'.config('twill.media_library.local_path'))),
        'cache' => env('GLIDE_CACHE', storage_path('app')),
        'cache_path_prefix' => env('GLIDE_CACHE_PATH_PREFIX', 'glide_cache'),
        'base_url' => env('GLIDE_BASE_URL', null),
        'base_path' => env('GLIDE_BASE_PATH', 'img'),
        'use_signed_urls' => env('GLIDE_USE_SIGNED_URLS', false),
    ],

    'file_library' => [
        'disk' => 'twill_file_library',
        'endpoint_type' => env('FILE_LIBRARY_ENDPOINT_TYPE', 's3'),
        'cascade_delete' => env('FILE_LIBRARY_CASCADE_DELETE', false),
        'local_path' => env('FILE_LIBRARY_LOCAL_PATH', 'uploads'),
        'file_service' => env('FILE_LIBRARY_FILE_SERVICE', 'A17\Twill\Services\FileLibrary\Disk'),
        'acl' => env('FILE_LIBRARY_ACL', 'public-read'),
        'filesize_limit' => env('FILE_LIBRARY_FILESIZE_LIMIT', 50),
        'prefix_uuid_with_local_path' => false,
        'allowed_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
    ],

    'media_library' => [
        'disk' => 'twill_media_library',
        'endpoint_type' => env('MEDIA_LIBRARY_ENDPOINT_TYPE', 's3'),
        'cascade_delete' => env('MEDIA_LIBRARY_CASCADE_DELETE', false),
        'local_path' => env('MEDIA_LIBRARY_LOCAL_PATH', 'uploads'),
        'image_service' => env('MEDIA_LIBRARY_IMAGE_SERVICE', 'A17\Twill\Services\MediaLibrary\Glide'),
        'acl' => env('MEDIA_LIBRARY_ACL', 'private'),
        'filesize_limit' => env('MEDIA_LIBRARY_FILESIZE_LIMIT', 50),
        'allowed_extensions' => ['svg', 'jpg', 'gif', 'png', 'jpeg'],
        'init_alt_text_from_filename' => true,
        'prefix_uuid_with_local_path' => config('twill.file_library.prefix_uuid_with_local_path', false),
        'translated_form_fields' => false,
    ],

];
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Talv Bansal](https://github.com/talvbansal)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
