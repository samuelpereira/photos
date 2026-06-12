<?php

namespace App\Livewire\Admin\Sessions;

use App\Models\Photo;
use App\Models\Session;
use App\Services\PhotoImageProcessor;
use App\Support\PhotoStorage;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Photos extends Component
{
    use WithFileUploads;

    public Session $session;

    /** @var array<int, TemporaryUploadedFile> */
    public array $uploads = [];

    /** @var array<int, string|null> */
    public array $captions = [];

    public function mount(Session $session): void
    {
        $this->session = $session;
        $this->captions = $session->photos->pluck('caption', 'id')->all();
    }

    public function updatedUploads(): void
    {
        $maxUploadKb = (int) config('photos.max_upload_kb', 25600);

        $this->validate([
            'uploads.*' => ['image', 'max:'.$maxUploadKb],
        ], [
            'uploads.*.max' => 'Cada foto pode ter no máximo '.intval($maxUploadKb / 1024).' MB.',
            'uploads.*.image' => 'Apenas imagens JPG, PNG ou WebP são aceites.',
        ]);

        $nextOrder = (int) $this->session->photos()->max('sort_order') + 1;
        $processor = app(PhotoImageProcessor::class);
        $uploadedCount = 0;
        $failedCount = 0;

        foreach ($this->uploads as $upload) {
            try {
                $localPath = $upload->getRealPath();

                if (! is_readable($localPath)) {
                    $failedCount++;

                    continue;
                }

                $extension = strtolower($upload->getClientOriginalExtension() ?: 'jpg');
                $stored = $processor->storeUploadedFile($localPath, $this->session->id, $extension);

                if ($stored['path'] === null || ($stored['thumbnail'] === null && $stored['display'] === null)) {
                    if ($stored['path'] !== null) {
                        PhotoStorage::originalDisk()->delete($stored['path']);
                    }
                    $failedCount++;

                    continue;
                }

                $this->session->photos()->create([
                    'path' => $stored['path'],
                    'thumbnail_path' => $stored['thumbnail'],
                    'display_path' => $stored['display'],
                    'sort_order' => $nextOrder++,
                ]);

                $uploadedCount++;
            } catch (\Throwable $exception) {
                report($exception);
                $failedCount++;
            }
        }

        $this->reset('uploads');
        $this->refreshSession();

        if ($uploadedCount > 0) {
            Flux::toast(
                variant: 'success',
                text: $uploadedCount.' '.($uploadedCount === 1 ? 'foto adicionada.' : 'fotos adicionadas.'),
            );
        }

        if ($failedCount > 0) {
            Flux::toast(
                variant: 'danger',
                text: $failedCount === 1
                    ? 'Não foi possível processar 1 foto. Verifica o tamanho ou o formato.'
                    : "Não foi possível processar {$failedCount} fotos. Verifica o tamanho ou o formato.",
            );
        }
    }

    public function updatedCaptions(?string $value, string $key): void
    {
        $photo = $this->session->photos()->findOrFail($key);
        $photo->update(['caption' => filled($value) ? $value : null]);
    }

    public function setCover(Photo $photo): void
    {
        $this->session->photos()->update(['is_cover' => false]);
        $photo->update(['is_cover' => true]);
        $this->refreshSession();

        Flux::toast(variant: 'success', text: 'Capa atualizada.');
    }

    public function toggleFeatured(Photo $photo): void
    {
        $photo->update(['is_featured' => ! $photo->is_featured]);
        $this->refreshSession();
    }

    public function deletePhoto(Photo $photo): void
    {
        app(PhotoImageProcessor::class)->deleteFiles($photo);

        $photo->delete();
        $this->refreshSession();

        Flux::toast(variant: 'success', text: 'Foto eliminada.');
    }

    /** @param  array<int, int|string>  $orderedIds */
    public function reorderPhotos(array $orderedIds): void
    {
        $orderedIds = array_map(intval(...), $orderedIds);
        $sessionPhotoIds = $this->session->photos()->pluck('id')->all();

        if (count($orderedIds) !== count($sessionPhotoIds) || array_diff($orderedIds, $sessionPhotoIds) !== []) {
            return;
        }

        foreach ($orderedIds as $order => $id) {
            Photo::query()
                ->where('id', $id)
                ->where('session_id', $this->session->id)
                ->update(['sort_order' => $order]);
        }

        $this->refreshSession();
    }

    private function refreshSession(): void
    {
        $this->session->refresh();
        $this->session->load('photos');
    }

    public function render(): View
    {
        return view('livewire.admin.sessions.photos')
            ->title('Fotos · '.$this->session->name);
    }
}
