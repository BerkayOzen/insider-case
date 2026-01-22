<?php

namespace App\Domains\League\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Team extends Model
{
    protected $fillable = [
        'season_id',
        'name',
        'power',
    ];

    protected $casts = [
        'power' => 'integer',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(LeagueMatch::class, 'home_team_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(LeagueMatch::class, 'away_team_id');
    }

    public function standing(): HasOne
    {
        return $this->hasOne(Standing::class);
    }
}
