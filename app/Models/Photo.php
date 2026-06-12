<?php

namespace App\Models;

use App\Support\PhotoStorage;
use Database\Factories\PhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $session_id
 * @property string $path
 * @property string|null $thumbnail_path
 * @property string|null $display_path
 * @property string|null $caption
 * @property int $sort_order
 * @property bool $is_cover
 * @property bool $is_featured
 */
class Photo extends Model
{
    /** @use HasFactory<PhotoFactory> */
    use HasFactory;

    protected $fillable = [
        'session_id',
        'path',
        'thumbnail_path',
        'display_path',
        'caption',
        'sort_order',
        'is_cover',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /** @return BelongsTo<Session, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    /** Versão pública com marca de água (lightbox). */
    public function url(): string
    {
        if ($this->display_path) {
            return PhotoStorage::url($this->display_path);
        }

        if ($this->thumbnail_path) {
            return PhotoStorage::url($this->thumbnail_path);
        }

        return $this->originalUrl();
    }

    /** Versão pública com marca de água (grelha, capas). */
    public function thumbnailUrl(): string
    {
        if ($this->thumbnail_path) {
            return PhotoStorage::url($this->thumbnail_path);
        }

        return $this->url();
    }

    /** Original sem marca de água (admin, rota autenticada). */
    public function originalUrl(): string
    {
        if (str_starts_with($this->path, 'http')) {
            return $this->path;
        }

        return route('admin.photos.original', $this);
    }
}
