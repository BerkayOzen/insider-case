<?php

namespace App\Domains\League\DTO;

use App\Domains\League\Models\LeagueMatch;
use App\Domains\League\Models\Season;
use App\Domains\League\Models\Standing;
use App\Domains\League\Models\Week;

class LeagueStateDTO
{
    public function __construct(private readonly array $payload)
    {
    }

    public static function fromSeason(Season $season, ?array $prediction = null): self
    {
        $season->loadMissing('teams');
        $teams = $season->teams->keyBy('id');

        $weeks = $season->weeks
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
            });

        $standings = $season->standings
            ->sort(function (Standing $a, Standing $b): int {
                if ($a->points !== $b->points) {
                    return $b->points <=> $a->points;
                }
                if ($a->gd !== $b->gd) {
                    return $b->gd <=> $a->gd;
                }
                if ($a->gf !== $b->gf) {
                    return $b->gf <=> $a->gf;
                }

                return strcmp($a->team->name, $b->team->name);
            })
            ->values()
            ->map(function (Standing $standing): array {
                return [
                    'id' => $standing->id,
                    'teamId' => $standing->team_id,
                    'teamName' => $standing->team->name,
                    'played' => $standing->played,
                    'won' => $standing->won,
                    'drawn' => $standing->drawn,
                    'lost' => $standing->lost,
                    'gf' => $standing->gf,
                    'ga' => $standing->ga,
                    'gd' => $standing->gd,
                    'points' => $standing->points,
                ];
            });

        return new self([
            'season' => [
                'id' => $season->id,
                'name' => $season->name,
                'currentWeek' => $season->current_week,
                'isFinished' => $season->is_finished,
            ],
            'currentWeek' => $season->current_week,
            'weeks' => $weeks,
            'standings' => $standings,
            'prediction' => $prediction,
        ]);
    }

    public function toArray(): array
    {
        return $this->payload;
    }
}
