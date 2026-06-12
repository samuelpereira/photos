<?php

use App\Models\Photo;
use App\Models\Session;
use App\Models\User;
use App\Services\PhotoImageProcessor;
use App\Support\PhotoStorage;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config([
        'photos.disk' => 'public',
        'photos.original_disk' => 'local',
        'photos.watermark_text' => '© Teste Fotógrafo',
        'photos.thumbnail_max_width' => 1200,
        'photos.display_max_width' => 2400,
    ]);
    Storage::fake('public');
    Storage::fake('local');
});

test('stores uploaded file with unpredictable separate paths', function () {
    $source = tempnam(sys_get_temp_dir(), 'photo_src_');
    $image = imagecreatetruecolor(1800, 1200);
    imagejpeg($image, $source, 90);
    imagedestroy($image);

    $processor = app(PhotoImageProcessor::class);
    $stored = $processor->storeUploadedFile($source, 1, 'jpg');

    @unlink($source);

    expect($stored['path'])->toStartWith('private/sessions/1/');
    expect($stored['thumbnail'])->toStartWith('public/1/thumbs/');
    expect($stored['display'])->toStartWith('public/1/display/');
    expect(basename($stored['path']))->not->toBe(basename($stored['thumbnail']));
    expect(basename($stored['path']))->not->toBe(basename($stored['display']));
    expect(Storage::disk('local')->exists($stored['path']))->toBeTrue();
    expect(Storage::disk('public')->exists($stored['thumbnail']))->toBeTrue();
    expect(Storage::disk('public')->exists($stored['display']))->toBeTrue();
    expect(Storage::disk('public')->exists($stored['path']))->toBeFalse();

    [$thumbnailWidth] = getimagesize(Storage::disk('public')->path($stored['thumbnail']));
    [$displayWidth] = getimagesize(Storage::disk('public')->path($stored['display']));

    expect($thumbnailWidth)->toBe(1200);
    expect($displayWidth)->toBe(1800);
});

test('stores originals on s3 under private sessions prefix', function () {
    config(['photos.disk' => 's3', 'photos.original_disk' => 's3']);
    Storage::fake('s3');

    $source = tempnam(sys_get_temp_dir(), 'photo_src_');
    $image = imagecreatetruecolor(800, 600);
    imagejpeg($image, $source, 90);
    imagedestroy($image);

    $stored = app(PhotoImageProcessor::class)->storeUploadedFile($source, 5, 'jpg');

    @unlink($source);

    expect($stored['path'])->toStartWith('private/sessions/5/');
    expect(Storage::disk('s3')->exists($stored['path']))->toBeTrue();
    expect(Storage::disk('s3')->exists($stored['thumbnail']))->toBeTrue();
    expect(Storage::disk('s3')->exists($stored['display']))->toBeTrue();
});

test('photo urls use the configured storage disk', function () {
    $photo = Photo::factory()->create([
        'path' => 'private/sessions/1/original.jpg',
        'thumbnail_path' => 'public/1/thumbs/thumb.jpg',
        'display_path' => 'public/1/display/display.jpg',
    ]);

    expect($photo->url())->toBe(PhotoStorage::url('public/1/display/display.jpg'));
    expect($photo->thumbnailUrl())->toBe(PhotoStorage::url('public/1/thumbs/thumb.jpg'));
    expect($photo->originalUrl())->toBe(route('admin.photos.original', $photo));
});

test('original photo is only served through authenticated route', function () {
    $session = Session::factory()->create();
    $photo = Photo::factory()->create([
        'session_id' => $session->id,
        'path' => 'private/sessions/1/test.jpg',
        'thumbnail_path' => 'public/1/thumbs/test.jpg',
        'display_path' => 'public/1/display/test.jpg',
    ]);

    Storage::disk('local')->put('private/sessions/1/test.jpg', 'original-image');

    $this->get(route('admin.photos.original', $photo))->assertRedirect();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.photos.original', $photo))
        ->assertOk();
});

test('livewire temporary upload limit matches photo upload config', function () {
    expect(config('livewire.temporary_file_upload.disk'))->toBe('local');
    expect(config('livewire.temporary_file_upload.rules'))->toContain('max:'.config('photos.max_upload_kb'));
});

test('deleting a session removes its storage folders', function () {
    config(['photos.disk' => 's3', 'photos.original_disk' => 's3']);
    Storage::fake('s3');

    $session = Session::factory()->create();
    $originalPath = "private/sessions/{$session->id}/photo.jpg";
    $thumbnailPath = "public/{$session->id}/thumbs/photo.jpg";
    $displayPath = "public/{$session->id}/display/photo.jpg";

    Storage::disk('s3')->put($originalPath, 'original');
    Storage::disk('s3')->put($thumbnailPath, 'thumbnail');
    Storage::disk('s3')->put($displayPath, 'display');

    Photo::factory()->create([
        'session_id' => $session->id,
        'path' => $originalPath,
        'thumbnail_path' => $thumbnailPath,
        'display_path' => $displayPath,
    ]);

    $session->delete();

    expect(Storage::disk('s3')->exists($originalPath))->toBeFalse();
    expect(Storage::disk('s3')->exists($thumbnailPath))->toBeFalse();
    expect(Storage::disk('s3')->exists($displayPath))->toBeFalse();
});

test('deletes original, thumbnail and display files for a photo', function () {
    Storage::disk('local')->put('private/sessions/1/photo.jpg', 'original');
    Storage::disk('public')->put('public/1/thumbs/photo.jpg', 'thumbnail');
    Storage::disk('public')->put('public/1/display/photo.jpg', 'display');

    $photo = Photo::factory()->make([
        'path' => 'private/sessions/1/photo.jpg',
        'thumbnail_path' => 'public/1/thumbs/photo.jpg',
        'display_path' => 'public/1/display/photo.jpg',
    ]);

    app(PhotoImageProcessor::class)->deleteFiles($photo);

    expect(Storage::disk('local')->exists('private/sessions/1/photo.jpg'))->toBeFalse();
    expect(Storage::disk('public')->exists('public/1/thumbs/photo.jpg'))->toBeFalse();
    expect(Storage::disk('public')->exists('public/1/display/photo.jpg'))->toBeFalse();
});
