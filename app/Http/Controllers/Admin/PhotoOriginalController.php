<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Support\PhotoStorage;
use Symfony\Component\HttpFoundation\Response;

class PhotoOriginalController extends Controller
{
    public function __invoke(Photo $photo): Response
    {
        if (str_starts_with($photo->path, 'http')) {
            abort(404);
        }

        $disk = PhotoStorage::originalDisk();

        if (! $disk->exists($photo->path)) {
            abort(404);
        }

        return $disk->response($photo->path);
    }
}
