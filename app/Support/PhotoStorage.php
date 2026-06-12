<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class PhotoStorage
{
    public static function publicDisk(): Filesystem
    {
        return Storage::disk(config('photos.disk', 'public'));
    }

    public static function originalDisk(): Filesystem
    {
        return Storage::disk(config('photos.original_disk', 'local'));
    }

    /** @deprecated Use publicDisk() or originalDisk() explicitly. */
    public static function disk(): Filesystem
    {
        return self::publicDisk();
    }

    public static function isLocal(): bool
    {
        return self::driverIsLocal(config('photos.disk', 'public'));
    }

    public static function isOriginalLocal(): bool
    {
        return self::driverIsLocal(config('photos.original_disk', 'local'));
    }

    public static function url(string $path): string
    {
        return self::publicUrl($path);
    }

    public static function publicUrl(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return self::publicDisk()->url($path);
    }

    public static function isOriginalPath(string $path): bool
    {
        return str_starts_with($path, 'private/sessions/');
    }

    private static function driverIsLocal(string $disk): bool
    {
        return config('filesystems.disks.'.$disk.'.driver') === 'local';
    }
}
