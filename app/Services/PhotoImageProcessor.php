<?php

namespace App\Services;

use App\Models\Photo;
use App\Support\PhotoStorage;
use GdImage;
use Illuminate\Support\Str;

class PhotoImageProcessor
{
    /**
     * @return array{original: string, thumbnail: string, display: string}
     */
    public function buildStoragePaths(int $sessionId, string $extension): array
    {
        $extension = strtolower(ltrim($extension, '.') ?: 'jpg');

        return [
            'original' => "private/sessions/{$sessionId}/".Str::uuid().'.'.$extension,
            'thumbnail' => "public/{$sessionId}/thumbs/".Str::uuid().'.'.$extension,
            'display' => "public/{$sessionId}/display/".Str::uuid().'.'.$extension,
        ];
    }

    /**
     * @return array{path: ?string, thumbnail: ?string, display: ?string}
     */
    public function storeUploadedFile(string $localSourcePath, int $sessionId, string $extension): array
    {
        $paths = $this->buildStoragePaths($sessionId, $extension);
        $contents = file_get_contents($localSourcePath);

        if ($contents === false) {
            return ['path' => null, 'thumbnail' => null, 'display' => null];
        }

        $this->storeOriginal($paths['original'], $contents);
        $variants = $this->createVariantsFromLocalFile($localSourcePath, $paths['thumbnail'], $paths['display']);

        if ($variants['thumbnail'] === null && $variants['display'] === null) {
            PhotoStorage::originalDisk()->delete($paths['original']);

            return ['path' => null, 'thumbnail' => null, 'display' => null];
        }

        return [
            'path' => $paths['original'],
            'thumbnail' => $variants['thumbnail'],
            'display' => $variants['display'],
        ];
    }

    /**
     * @return array{thumbnail: ?string, display: ?string}
     */
    public function regenerateVariants(Photo $photo): array
    {
        [$sourcePath, $deleteSource] = $this->resolveSourcePath($photo->path);

        if ($sourcePath === null) {
            return ['thumbnail' => null, 'display' => null];
        }

        $extension = pathinfo($photo->path, PATHINFO_EXTENSION) ?: 'jpg';
        $paths = $this->buildStoragePaths($photo->session_id, $extension);
        $oldVariantPaths = [$photo->thumbnail_path, $photo->display_path];

        try {
            $variants = $this->createVariantsFromLocalFile($sourcePath, $paths['thumbnail'], $paths['display']);
        } finally {
            if ($deleteSource) {
                @unlink($sourcePath);
            }
        }

        if ($variants['thumbnail'] === null && $variants['display'] === null) {
            return ['thumbnail' => null, 'display' => null];
        }

        $this->deleteStoredPaths($oldVariantPaths);

        return $variants;
    }

    public function deleteFiles(Photo $photo): void
    {
        if (str_starts_with($photo->path, 'http')) {
            return;
        }

        $this->deleteStoredPaths([
            $photo->path,
            $photo->thumbnail_path,
            $photo->display_path,
        ]);
    }

    public function deleteSessionStorage(int $sessionId): void
    {
        PhotoStorage::publicDisk()->deleteDirectory("public/{$sessionId}");
        PhotoStorage::originalDisk()->deleteDirectory("private/sessions/{$sessionId}");
    }

    /**
     * @return array{thumbnail: ?string, display: ?string}
     */
    private function createVariantsFromLocalFile(string $localSourcePath, string $thumbnailPath, string $displayPath): array
    {
        $thumbnailMax = (int) config('photos.thumbnail_max_width', 1200);
        $displayMax = (int) config('photos.display_max_width', 2400);

        $imageInfo = @getimagesize($localSourcePath);

        if ($imageInfo === false) {
            return ['thumbnail' => null, 'display' => null];
        }

        [, , $type] = $imageInfo;
        $sourceImage = $this->createImageResource($localSourcePath, $type);

        if ($sourceImage === null) {
            return ['thumbnail' => null, 'display' => null];
        }

        $workingImage = $this->resizeImageToMaxWidth($sourceImage, $displayMax, $type);

        if ($workingImage !== $sourceImage) {
            imagedestroy($sourceImage);
        }

        $thumbnailVariant = $this->resizeImageToMaxWidth($workingImage, $thumbnailMax, $type);
        $this->applyTextWatermark($thumbnailVariant);
        $this->storePublicVariant($thumbnailPath, $thumbnailVariant, $type);
        imagedestroy($thumbnailVariant);

        $this->applyTextWatermark($workingImage);
        $this->storePublicVariant($displayPath, $workingImage, $type);
        imagedestroy($workingImage);

        return ['thumbnail' => $thumbnailPath, 'display' => $displayPath];
    }

    private function storeOriginal(string $path, string $contents): void
    {
        $disk = PhotoStorage::originalDisk();

        if (PhotoStorage::isOriginalLocal()) {
            $disk->makeDirectory(dirname($path));
            $disk->put($path, $contents);

            return;
        }

        $disk->put($path, $contents, ['visibility' => 'private']);
    }

    /** @param  array<int, string|null>  $paths */
    private function deleteStoredPaths(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path === null || str_starts_with($path, 'http')) {
                continue;
            }

            if (PhotoStorage::isOriginalPath($path)) {
                PhotoStorage::originalDisk()->delete($path);

                continue;
            }

            PhotoStorage::publicDisk()->delete($path);
        }
    }

    /** @return array{0: ?string, 1: bool} */
    private function resolveSourcePath(string $path): array
    {
        if (str_starts_with($path, 'http')) {
            return [null, false];
        }

        $disk = PhotoStorage::originalDisk();

        if (PhotoStorage::isOriginalLocal()) {
            $localPath = $disk->path($path);

            return [is_file($localPath) ? $localPath : null, false];
        }

        if (! $disk->exists($path)) {
            return [null, false];
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'photo_src_');
        file_put_contents($tempPath, $disk->get($path));

        return [$tempPath, true];
    }

    private function storePublicVariant(string $variantPath, GdImage $variant, int $type): void
    {
        $tempPath = $this->temporaryOutputPath($type);

        try {
            $this->saveImage($variant, $tempPath, $type);

            $disk = PhotoStorage::publicDisk();

            if (PhotoStorage::isLocal()) {
                $disk->makeDirectory(dirname($variantPath));

                if (! rename($tempPath, $disk->path($variantPath))) {
                    copy($tempPath, $disk->path($variantPath));
                }

                return;
            }

            $contents = file_get_contents($tempPath);

            if ($contents === false) {
                return;
            }

            $disk->put($variantPath, $contents, ['visibility' => 'public']);
        } finally {
            @unlink($tempPath);
        }
    }

    private function temporaryOutputPath(int $type): string
    {
        $extension = match ($type) {
            IMAGETYPE_PNG => '.png',
            IMAGETYPE_WEBP => '.webp',
            IMAGETYPE_GIF => '.gif',
            default => '.jpg',
        };

        $tempPath = tempnam(sys_get_temp_dir(), 'photo_out_');
        @unlink($tempPath);

        return $tempPath.$extension;
    }

    private function applyTextWatermark(GdImage $image): void
    {
        $text = Str::of(config('photos.watermark_text'))->trim()->toString();

        if ($text === '') {
            return;
        }

        if (config('photos.watermark_lowercase', true)) {
            $text = mb_strtolower($text);
        }

        imagealphablending($image, true);

        $width = imagesx($image);
        $height = imagesy($image);
        $fontSize = max(16, (int) round($width / 22));
        $margin = max(14, (int) round($height * 0.03));
        $fontFile = $this->resolveFontPath();

        if ($fontFile !== null) {
            $box = imagettfbbox($fontSize, 0, $fontFile, $text);

            if ($box === false) {
                return;
            }

            $textWidth = abs($box[2] - $box[0]);
            $x = (int) (($width - $textWidth) / 2);
            $y = $height - $margin;

            $shadow = imagecolorallocatealpha($image, 0, 0, 0, 50);
            $color = imagecolorallocatealpha($image, 255, 255, 255, 10);

            if ($shadow !== false && $color !== false) {
                $this->drawBoldText($image, $fontSize, $x + 2, $y + 2, $shadow, $fontFile, $text);
                $this->drawBoldText($image, $fontSize, $x, $y, $color, $fontFile, $text);
            }

            return;
        }

        $builtInFont = 5;
        $textWidth = strlen($text) * imagefontwidth($builtInFont);
        $x = (int) (($width - $textWidth) / 2);
        $y = $height - imagefontheight($builtInFont) - $margin;
        $shadow = imagecolorallocate($image, 0, 0, 0);
        $color = imagecolorallocate($image, 255, 255, 255);

        if ($shadow !== false && $color !== false) {
            imagestring($image, $builtInFont, $x + 1, $y + 1, $text, $shadow);
            imagestring($image, $builtInFont, $x, $y, $text, $color);
        }
    }

    private function drawBoldText(GdImage $image, int $fontSize, int $x, int $y, int $color, string $fontFile, string $text): void
    {
        foreach ([[0, 0], [1, 0], [-1, 0], [0, 1]] as [$offsetX, $offsetY]) {
            imagettftext($image, $fontSize, 0, $x + $offsetX, $y + $offsetY, $color, $fontFile, $text);
        }
    }

    private function resolveFontPath(): ?string
    {
        $configured = config('photos.watermark_font');

        if (is_string($configured) && is_file($configured)) {
            return $configured;
        }

        foreach ([
            resource_path('fonts/DancingScript-Regular.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
        ] as $font) {
            if (is_file($font)) {
                return $font;
            }
        }

        return null;
    }

    private function resizeImageToMaxWidth(GdImage $sourceImage, int $maxWidth, int $type): GdImage
    {
        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);

        if ($width <= $maxWidth) {
            return $sourceImage;
        }

        $newWidth = max(1, $maxWidth);
        $newHeight = max(1, (int) round($height * ($maxWidth / $width)));
        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true)) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);

            if ($transparent !== false) {
                imagefill($resized, 0, 0, $transparent);
            }
        }

        imagecopyresampled(
            $resized,
            $sourceImage,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height,
        );

        return $resized;
    }

    private function createImageResource(string $path, int $type): ?GdImage
    {
        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            default => false,
        };

        return $image instanceof GdImage ? $image : null;
    }

    private function saveImage(GdImage $image, string $path, int $type): void
    {
        $quality = config('photos.thumbnail_quality', 82);

        match ($type) {
            IMAGETYPE_JPEG => imagejpeg($image, $path, $quality),
            IMAGETYPE_PNG => imagepng($image, $path, (int) round(9 - ($quality / 100) * 9)),
            IMAGETYPE_WEBP => function_exists('imagewebp')
                ? imagewebp($image, $path, $quality)
                : imagejpeg($image, $path, $quality),
            IMAGETYPE_GIF => imagegif($image, $path),
            default => imagejpeg($image, $path, $quality),
        };
    }
}
