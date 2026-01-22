<?php

namespace App\Domains\League\Actions;

use App\Domains\League\Models\Team;

class GetTeamById
{
    /**
     * @return array<string, mixed>
     */
    public function execute(Team $team): array
    {
        $team->loadMissing([
            'season',
            'homeMatches.week',
            'homeMatches.homeTeam',
            'homeMatches.awayTeam',
            'awayMatches.week',
            'awayMatches.homeTeam',
            'awayMatches.awayTeam',
        ]);

        $matches = $team->homeMatches
            ->merge($team->awayMatches)
            ->sortBy(fn ($match) => [$match->week?->number ?? 0, $match->id])
            ->values()
            ->map(function ($match) use ($team): array {
                $isHome = $match->home_team_id === $team->id;
                $opponent = $isHome ? $match->awayTeam : $match->homeTeam;

                return [
                    'id' => $match->id,
                    'week' => $match->week?->number,
                    'isHome' => $isHome,
                    'opponent' => $opponent ? ['id' => $opponent->id, 'name' => $opponent->name] : null,
                    'homeScore' => $match->home_score,
                    'awayScore' => $match->away_score,
                    'isPlayed' => $match->is_played,
                ];
            })
            ->all();

        return [
            'team' => $this->serializeTeam($team),
            'matches' => $matches,
        ];
    }

    private function serializeTeam(Team $team): array
    {
        return [
            'id' => $team->id,
            'seasonId' => $team->season_id,
            'name' => $team->name,
            'power' => $team->power,
            'season' => $team->relationLoaded('season') && $team->season ? [
                'id' => $team->season->id,
                'name' => $team->season->name,
                'currentWeek' => $team->season->current_week,
                'isFinished' => $team->season->is_finished,
            ] : null,
        ];
    }
}
