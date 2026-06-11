<?php

namespace App\Services;

use App\Models\Photo;
use App\Support\PhotoStorage;
use GdImage;
use Illuminate\Support\Str;

class PhotoImageProcessor
{
    public function generateThumbnail(string $originalPath): ?string
    {
        return $this->generateVariant(
            $originalPath,
            $this->thumbnailPathFor($originalPath),
            config('photos.thumbnail_max_width', 1200),
        );
    }

    public function generateDisplay(string $originalPath): ?string
    {
        return $this->generateVariant(
            $originalPath,
            $this->displayPathFor($originalPath),
            config('photos.display_max_width', 2400),
        );
    }

    public function thumbnailPathFor(string $originalPath): string
    {
        return dirname($originalPath).'/thumbs/'.basename($originalPath);
    }

    public function displayPathFor(string $originalPath): string
    {
        return dirname($originalPath).'/display/'.basename($originalPath);
    }

    public function deleteFiles(Photo $photo): void
    {
        $disk = PhotoStorage::disk();

        foreach ([$photo->path, $photo->thumbnail_path, $photo->display_path] as $path) {
            if ($path && ! str_starts_with($path, 'http')) {
                $disk->delete($path);
            }
        }
    }

    private function generateVariant(string $originalPath, string $variantPath, int $maxWidth): ?string
    {
        [$sourcePath, $deleteSource] = $this->resolveSourcePath($originalPath);

        if ($sourcePath === null) {
            return null;
        }

        try {
            $imageInfo = @getimagesize($sourcePath);

            if ($imageInfo === false) {
                return null;
            }

            [$width, $height, $type] = $imageInfo;
            $sourceImage = $this->createImageResource($sourcePath, $type);

            if ($sourceImage === null) {
                return null;
            }

            $scale = min(1, $maxWidth / $width);
            $newWidth = max(1, (int) round($width * $scale));
            $newHeight = max(1, (int) round($height * $scale));

            $variant = imagecreatetruecolor($newWidth, $newHeight);

            if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true)) {
                imagealphablending($variant, false);
                imagesavealpha($variant, true);
                $transparent = imagecolorallocatealpha($variant, 0, 0, 0, 127);

                if ($transparent !== false) {
                    imagefill($variant, 0, 0, $transparent);
                }
            }

            imagecopyresampled(
                $variant,
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

            $this->applyTextWatermark($variant);
            $this->storeVariant($variantPath, $variant, $type);

            imagedestroy($sourceImage);
            imagedestroy($variant);

            return $variantPath;
        } finally {
            if ($deleteSource) {
                @unlink($sourcePath);
            }
        }
    }

    /** @return array{0: ?string, 1: bool} */
    private function resolveSourcePath(string $path): array
    {
        $disk = PhotoStorage::disk();

        if (PhotoStorage::isLocal()) {
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

    private function storeVariant(string $variantPath, GdImage $variant, int $type): void
    {
        $tempPath = $this->temporaryOutputPath($type);

        try {
            $this->saveImage($variant, $tempPath, $type);

            $disk = PhotoStorage::disk();

            if (PhotoStorage::isLocal()) {
                $disk->makeDirectory(dirname($variantPath));

                if (! rename($tempPath, $disk->path($variantPath))) {
                    copy($tempPath, $disk->path($variantPath));
                }

                return;
            }

            $disk->put($variantPath, file_get_contents($tempPath), ['visibility' => 'public']);
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
