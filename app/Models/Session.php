<?php

namespace App\Models;

use App\Services\PhotoImageProcessor;
use Database\Factories\SessionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $type_id
 * @property string|null $description
 * @property Carbon $session_date
 * @property bool $is_published
 */
class Session extends Model
{
    /** @use HasFactory<SessionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type_id',
        'description',
        'session_date',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Session $session): void {
            $processor = app(PhotoImageProcessor::class);
            $session->loadMissing('photos');

            foreach ($session->photos as $photo) {
                $processor->deleteFiles($photo);
            }

            $processor->deleteSessionStorage($session->id);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsTo<SessionType, $this> */
    public function sessionType(): BelongsTo
    {
        return $this->belongsTo(SessionType::class, 'type_id');
    }

    /** @return HasMany<Photo, $this> */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('sort_order');
    }

    /** @return HasOne<Photo, $this> */
    public function coverPhoto(): HasOne
    {
        return $this->hasOne(Photo::class)->where('is_cover', true);
    }

    /** @return HasMany<Game, $this> */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    /**
     * @param  Builder<Session>  $query
     * @return Builder<Session>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function coverUrl(): ?string
    {
        $photo = $this->coverPhoto ?? $this->photos->first();

        return $photo?->thumbnailUrl();
    }

    public function typeLabel(): string
    {
        return $this->sessionType->name ?? '';
    }
}
