<?php

use Illuminate\Support\Facades\Storage;

/**
 * Overwrite the s3Endpoint function provided by twill until this is patched...
 *
 * @param string $disk
 * @return string
 */
function s3Endpoint($disk = 'libraries')
{
    $scheme = config("filesystems.disks.{$disk}.use_https") ? 'https://' : '';

    return $scheme.config("filesystems.disks.{$disk}.bucket").'.'. Storage::disk($disk)->getAdapter()->getClient()->getEndpoint()->getHost();
}
