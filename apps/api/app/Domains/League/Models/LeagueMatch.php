<?php

namespace App\Domains\League\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueMatch extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'season_id',
        'week_id',
        'home_team_id',
        'away_team_id',
        'home_score',
        'away_score',
        'is_played',
    ];

    protected $casts = [
        'home_score' => 'integer',
        'away_score' => 'integer',
        'is_played' => 'boolean',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function week(): BelongsTo
    {
        return $this->belongsTo(Week::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }
}
