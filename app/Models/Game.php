<?php

namespace App\Models;

use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $session_id
 * @property int|null $competition_id
 * @property int|null $category_id
 * @property int|null $home_team_id
 * @property int|null $away_team_id
 * @property string $home_team
 * @property string $away_team
 * @property string|null $competition
 * @property string|null $category
 * @property string|null $venue
 * @property Carbon $tip_off_at
 * @property int|null $home_score
 * @property int|null $away_score
 */
class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    protected $fillable = [
        'session_id',
        'competition_id',
        'category_id',
        'home_team_id',
        'away_team_id',
        'home_team',
        'away_team',
        'competition',
        'category',
        'venue',
        'tip_off_at',
        'home_score',
        'away_score',
    ];

    protected function casts(): array
    {
        return [
            'tip_off_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Session, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    /** @return BelongsTo<Competition, $this> */
    public function competitionRelation(): BelongsTo
    {
        return $this->belongsTo(Competition::class, 'competition_id');
    }

    /** @return BelongsTo<Category, $this> */
    public function categoryRelation(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /** @return BelongsTo<Team, $this> */
    public function homeTeamRelation(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /** @return BelongsTo<Team, $this> */
    public function awayTeamRelation(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    /**
     * @param  Builder<Game>  $query
     * @return Builder<Game>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('tip_off_at', '>=', now())->orderBy('tip_off_at');
    }

    /**
     * @param  Builder<Game>  $query
     * @return Builder<Game>
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->where('tip_off_at', '<', now())->orderByDesc('tip_off_at');
    }

    /**
     * @param  Builder<Game>  $query
     * @return Builder<Game>
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query
            ->whereBetween('tip_off_at', [now(), now()->endOfWeek()->addWeek()])
            ->orderBy('tip_off_at');
    }

    public function isPast(): bool
    {
        return $this->tip_off_at->isPast();
    }

    public function hasResult(): bool
    {
        return $this->home_score !== null && $this->away_score !== null;
    }
}
