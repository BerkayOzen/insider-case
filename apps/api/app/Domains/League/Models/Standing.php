<?php

namespace App\Domains\League\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Standing extends Model
{
    protected $fillable = [
        'season_id',
        'team_id',
        'played',
        'won',
        'drawn',
        'lost',
        'gf',
        'ga',
        'gd',
        'points',
    ];

    protected $casts = [
        'played' => 'integer',
        'won' => 'integer',
        'drawn' => 'integer',
        'lost' => 'integer',
        'gf' => 'integer',
        'ga' => 'integer',
        'gd' => 'integer',
        'points' => 'integer',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
