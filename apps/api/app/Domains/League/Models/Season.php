<?php

namespace App\Domains\League\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    protected $fillable = [
        'name',
        'current_week',
        'is_finished',
    ];

    protected $casts = [
        'current_week' => 'integer',
        'is_finished' => 'boolean',
    ];

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function weeks(): HasMany
    {
        return $this->hasMany(Week::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(LeagueMatch::class);
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class);
    }
}
