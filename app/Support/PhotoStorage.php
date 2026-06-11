<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class PhotoStorage
{
    public static function disk(): Filesystem
    {
        return Storage::disk(config('photos.disk', 'public'));
    }

    public static function isLocal(): bool
    {
        return config('filesystems.disks.'.config('photos.disk', 'public').'.driver') === 'local';
    }

    public static function url(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return self::disk()->url($path);
    }
}
