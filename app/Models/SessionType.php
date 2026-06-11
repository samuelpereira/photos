<?php

namespace App\Models;

use Database\Factories\SessionTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $color
 */
class SessionType extends Model
{
    /** @use HasFactory<SessionTypeFactory> */
    use HasFactory;

    public const array COLORS = [
        'orange' => 'Laranja',
        'fuchsia' => 'Fúcsia',
        'blue' => 'Azul',
        'green' => 'Verde',
        'zinc' => 'Cinzento',
    ];

    protected $fillable = [
        'name',
        'slug',
        'color',
    ];

    /** @return HasMany<Session, $this> */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'type_id');
    }
}
