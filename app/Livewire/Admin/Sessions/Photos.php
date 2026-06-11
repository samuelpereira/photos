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
        $this->validate([
            'uploads.*' => ['image', 'max:15360'],
        ]);

        $nextOrder = (int) $this->session->photos()->max('sort_order') + 1;

        $processor = app(PhotoImageProcessor::class);

        foreach ($this->uploads as $upload) {
            $path = $upload->store("sessions/{$this->session->id}", config('photos.disk', 'public'));

            if (! is_string($path)) {
                continue;
            }

            if (! PhotoStorage::isLocal()) {
                PhotoStorage::disk()->setVisibility($path, 'public');
            }

            $this->session->photos()->create([
                'path' => $path,
                'thumbnail_path' => $processor->generateThumbnail($path),
                'display_path' => $processor->generateDisplay($path),
                'sort_order' => $nextOrder++,
            ]);
        }

        $count = count($this->uploads);
        $this->reset('uploads');
        $this->refreshSession();

        Flux::toast(variant: 'success', text: $count.' '.($count === 1 ? 'foto adicionada.' : 'fotos adicionadas.'));
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
