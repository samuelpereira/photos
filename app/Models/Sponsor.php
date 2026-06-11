<?php

namespace App\Models;

use App\Support\PhotoStorage;
use Database\Factories\SponsorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $url
 * @property string|null $logo_path
 * @property int $sort_order
 * @property bool $is_active
 */
class Sponsor extends Model
{
    /** @use HasFactory<SponsorFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'logo_path',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Sponsor>  $query
     * @return Builder<Sponsor>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function logoUrl(): ?string
    {
        if (! filled($this->logo_path)) {
            return null;
        }

        return PhotoStorage::url($this->logo_path);
    }
}
