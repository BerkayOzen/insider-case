<?php

namespace App\Domains\League\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Week extends Model
{
    protected $fillable = [
        'season_id',
        'number',
        'is_played',
    ];

    protected $casts = [
        'number' => 'integer',
        'is_played' => 'boolean',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(LeagueMatch::class);
    }
}
