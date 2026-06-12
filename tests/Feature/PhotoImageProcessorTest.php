<?php

use App\Models\Photo;
use App\Services\PhotoImageProcessor;
use App\Support\PhotoStorage;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config(['photos.disk' => 'public', 'photos.watermark_text' => '© Teste Fotógrafo']);
    Storage::fake('public');
});

test('generates a resized thumbnail from an uploaded photo', function () {
    $originalPath = 'sessions/1/photo.jpg';
    $source = Storage::disk('public')->path($originalPath);
    Storage::disk('public')->makeDirectory('sessions/1');

    $image = imagecreatetruecolor(1800, 1200);
    imagejpeg($image, $source, 90);
    imagedestroy($image);

    $processor = app(PhotoImageProcessor::class);
    $thumbnailPath = $processor->generateThumbnail($originalPath);

    expect($thumbnailPath)->toBe('sessions/1/thumbs/photo.jpg');
    expect(Storage::disk('public')->exists($thumbnailPath))->toBeTrue();

    [$width] = getimagesize(Storage::disk('public')->path($thumbnailPath));

    expect($width)->toBe(1200);
});

test('generates a display variant for the lightbox', function () {
    $originalPath = 'sessions/1/photo.jpg';
    $source = Storage::disk('public')->path($originalPath);
    Storage::disk('public')->makeDirectory('sessions/1');

    $image = imagecreatetruecolor(2600, 1700);
    imagejpeg($image, $source, 90);
    imagedestroy($image);

    $processor = app(PhotoImageProcessor::class);
    $displayPath = $processor->generateDisplay($originalPath);

    expect($displayPath)->toBe('sessions/1/display/photo.jpg');
    expect(Storage::disk('public')->exists($displayPath))->toBeTrue();

    [$width] = getimagesize(Storage::disk('public')->path($displayPath));

    expect($width)->toBeLessThanOrEqual(2400);
    expect($width)->toBeGreaterThan(2000);
});

test('photo urls use the configured storage disk', function () {
    $photo = Photo::factory()->make([
        'path' => 'sessions/1/photo.jpg',
        'thumbnail_path' => 'sessions/1/thumbs/photo.jpg',
        'display_path' => 'sessions/1/display/photo.jpg',
    ]);

    expect($photo->url())->toBe(PhotoStorage::url('sessions/1/display/photo.jpg'));
    expect($photo->thumbnailUrl())->toBe(PhotoStorage::url('sessions/1/thumbs/photo.jpg'));
    expect($photo->originalUrl())->toBe(PhotoStorage::url('sessions/1/photo.jpg'));
});

test('livewire temporary upload limit matches photo upload config', function () {
    expect(config('livewire.temporary_file_upload.disk'))->toBe('local');
    expect(config('livewire.temporary_file_upload.rules'))->toContain('max:'.config('photos.max_upload_kb'));
});

test('stores uploaded file and generates variants from local source', function () {
    $originalPath = 'sessions/1/large-photo.jpg';
    $source = Storage::disk('public')->path($originalPath);
    Storage::disk('public')->makeDirectory('sessions/1');

    $image = imagecreatetruecolor(6000, 4000);
    imagejpeg($image, $source, 90);
    imagedestroy($image);

    $processor = app(PhotoImageProcessor::class);
    $variants = $processor->storeUploadedFile($source, $originalPath);

    expect($variants['thumbnail'])->toBe('sessions/1/thumbs/large-photo.jpg');
    expect($variants['display'])->toBe('sessions/1/display/large-photo.jpg');
    expect(Storage::disk('public')->exists($variants['thumbnail']))->toBeTrue();
    expect(Storage::disk('public')->exists($variants['display']))->toBeTrue();

    [$thumbnailWidth] = getimagesize(Storage::disk('public')->path($variants['thumbnail']));
    [$displayWidth] = getimagesize(Storage::disk('public')->path($variants['display']));

    expect($thumbnailWidth)->toBe(1200);
    expect($displayWidth)->toBe(2400);
});

test('deletes original, thumbnail and display files for a photo', function () {
    Storage::disk('public')->put('sessions/1/photo.jpg', 'original');
    Storage::disk('public')->put('sessions/1/thumbs/photo.jpg', 'thumbnail');
    Storage::disk('public')->put('sessions/1/display/photo.jpg', 'display');

    $photo = Photo::factory()->make([
        'path' => 'sessions/1/photo.jpg',
        'thumbnail_path' => 'sessions/1/thumbs/photo.jpg',
        'display_path' => 'sessions/1/display/photo.jpg',
    ]);

    app(PhotoImageProcessor::class)->deleteFiles($photo);

    expect(Storage::disk('public')->exists('sessions/1/photo.jpg'))->toBeFalse();
    expect(Storage::disk('public')->exists('sessions/1/thumbs/photo.jpg'))->toBeFalse();
    expect(Storage::disk('public')->exists('sessions/1/display/photo.jpg'))->toBeFalse();
});
