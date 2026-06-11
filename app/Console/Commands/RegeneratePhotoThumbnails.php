<?php

namespace App\Console\Commands;

use App\Models\Photo;
use App\Services\PhotoImageProcessor;
use Illuminate\Console\Command;

class RegeneratePhotoThumbnails extends Command
{
    protected $signature = 'photos:regenerate-thumbnails {--force : Regenera todas as variantes, mesmo as que já existem}';

    protected $description = 'Gera thumbnails e versões de exibição com marca de água';

    public function handle(PhotoImageProcessor $processor): int
    {
        $query = Photo::query()->where('path', 'not like', 'http%');

        if (! $this->option('force')) {
            $query->where(function ($query) {
                $query->whereNull('thumbnail_path')->orWhereNull('display_path');
            });
        }

        $photos = $query->get();

        if ($photos->isEmpty()) {
            $this->info('Não há fotos por processar.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($photos->count());
        $bar->start();

        $processed = 0;

        foreach ($photos as $photo) {
            $thumbnailPath = $processor->generateThumbnail($photo->path);
            $displayPath = $processor->generateDisplay($photo->path);

            if ($thumbnailPath || $displayPath) {
                $photo->update([
                    'thumbnail_path' => $thumbnailPath ?? $photo->thumbnail_path,
                    'display_path' => $displayPath ?? $photo->display_path,
                ]);
                $processed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Variantes geradas: {$processed}/{$photos->count()}.");

        return self::SUCCESS;
    }
}
