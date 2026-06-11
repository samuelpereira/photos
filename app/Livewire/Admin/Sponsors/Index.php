<?php

namespace App\Livewire\Admin\Sponsors;

use App\Models\Sponsor;
use App\Support\PhotoStorage;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Patrocinadores')]
class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    public ?int $editingId = null;

    public string $name = '';

    public string $url = '';

    public bool $is_active = true;

    public ?TemporaryUploadedFile $logo = null;

    public ?string $currentLogoPath = null;

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'url', 'logo', 'currentLogoPath']);
        $this->is_active = true;
        $this->resetValidation();

        Flux::modal('sponsor-form')->show();
    }

    public function edit(int $sponsorId): void
    {
        $sponsor = Sponsor::query()->findOrFail($sponsorId);

        $this->editingId = $sponsor->id;
        $this->name = $sponsor->name;
        $this->url = $sponsor->url ?? '';
        $this->is_active = $sponsor->is_active;
        $this->currentLogoPath = $sponsor->logo_path;
        $this->reset('logo');
        $this->resetValidation();

        Flux::modal('sponsor-form')->show();
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['boolean'],
        ];

        if (! $this->editingId) {
            $rules['logo'] = ['required', 'file', 'mimes:jpeg,jpg,png,webp,svg', 'max:5120'];
        } else {
            $rules['logo'] = ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,svg', 'max:5120'];
        }

        $validated = $this->validate($rules);

        $logoPath = $this->currentLogoPath;

        if ($this->logo) {
            if ($logoPath && ! str_starts_with($logoPath, 'http')) {
                PhotoStorage::disk()->delete($logoPath);
            }

            $storedPath = $this->logo->store('sponsors', config('photos.disk', 'public'));

            if ($storedPath === false) {
                return;
            }

            $logoPath = $storedPath;

            if (! PhotoStorage::isLocal()) {
                PhotoStorage::disk()->setVisibility($logoPath, 'public');
            }
        }

        if (! $logoPath) {
            return;
        }

        $data = [
            'name' => $validated['name'],
            'url' => filled($validated['url']) ? $validated['url'] : null,
            'logo_path' => $logoPath,
            'is_active' => $validated['is_active'],
        ];

        if ($this->editingId) {
            Sponsor::query()->findOrFail($this->editingId)->update($data);
        } else {
            $data['sort_order'] = (int) Sponsor::query()->max('sort_order') + 1;
            Sponsor::query()->create($data);
        }

        Flux::modal('sponsor-form')->close();
        Flux::toast(variant: 'success', text: $this->editingId ? 'Patrocinador atualizado.' : 'Patrocinador criado.');
    }

    public function delete(int $sponsorId): void
    {
        $sponsor = Sponsor::query()->findOrFail($sponsorId);

        if (filled($sponsor->logo_path) && ! str_starts_with($sponsor->logo_path, 'http')) {
            PhotoStorage::disk()->delete($sponsor->logo_path);
        }

        $sponsor->delete();

        Flux::toast(variant: 'success', text: 'Patrocinador eliminado.');
    }

    public function render(): View
    {
        return view('livewire.admin.sponsors.index', [
            'sponsors' => Sponsor::query()
                ->orderBy('sort_order')
                ->paginate(10),
        ]);
    }
}
