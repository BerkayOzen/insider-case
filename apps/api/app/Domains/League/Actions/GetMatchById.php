<?php

namespace App\Domains\League\Actions;

use App\Domains\League\Models\LeagueMatch;

class GetMatchById
{
    /**
     * @return array<string, mixed>
     */
    public function execute(LeagueMatch $match): array
    {
        $match->loadMissing(['season', 'week', 'homeTeam', 'awayTeam']);

        return [
            'id' => $match->id,
            'seasonId' => $match->season_id,
            'week' => $match->week ? [
                'id' => $match->week->id,
                'number' => $match->week->number,
                'isPlayed' => $match->week->is_played,
            ] : null,
            'homeTeam' => $match->homeTeam ? [
                'id' => $match->homeTeam->id,
                'name' => $match->homeTeam->name,
            ] : null,
            'awayTeam' => $match->awayTeam ? [
                'id' => $match->awayTeam->id,
                'name' => $match->awayTeam->name,
            ] : null,
            'homeScore' => $match->home_score,
            'awayScore' => $match->away_score,
            'isPlayed' => $match->is_played,
        ];
    }
}
