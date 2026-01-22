<?php

namespace App\Domains\League\Actions;

use App\Domains\League\Models\LeagueMatch;
use App\Domains\League\Models\Season;
use App\Domains\League\Models\Week;

class GetFixtures
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(?Season $season = null): array
    {
        $season = $season ?? Season::query()->latest('id')->firstOrFail();
        $season->loadMissing(['teams', 'weeks.matches.homeTeam', 'weeks.matches.awayTeam']);
        $teams = $season->teams->keyBy('id');

        return $season->weeks
            ->sortBy('number')
            ->values()
            ->map(function (Week $week) use ($teams): array {
                $usedTeamIds = $week->matches
                    ->flatMap(fn (LeagueMatch $match) => [$match->home_team_id, $match->away_team_id])
                    ->filter()
                    ->unique();
                $byeTeams = $teams
                    ->reject(fn ($team) => $usedTeamIds->contains($team->id))
                    ->map(fn ($team) => ['id' => $team->id, 'name' => $team->name])
                    ->values();

                return [
                    'id' => $week->id,
                    'number' => $week->number,
                    'isPlayed' => $week->is_played,
                    'byeTeams' => $byeTeams,
                    'matches' => $week->matches->map(function (LeagueMatch $match): array {
                        return [
                            'id' => $match->id,
                            'homeTeamId' => $match->home_team_id,
                            'awayTeamId' => $match->away_team_id,
                            'homeScore' => $match->home_score,
                            'awayScore' => $match->away_score,
                            'isPlayed' => $match->is_played,
                            'homeTeam' => $match->homeTeam ? [
                                'id' => $match->homeTeam->id,
                                'name' => $match->homeTeam->name,
                            ] : null,
                            'awayTeam' => $match->awayTeam ? [
                                'id' => $match->awayTeam->id,
                                'name' => $match->awayTeam->name,
                            ] : null,
                        ];
                    })->values(),
                ];
            })
            ->all();
    }
}
