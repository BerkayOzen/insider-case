<?php

namespace App\Domains\League\Actions;

use App\Domains\League\Models\Team;

class GetAllTeams
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(): array
    {
        return Team::query()
            ->with('season')
            ->orderBy('season_id')
            ->orderBy('name')
            ->get()
            ->map(fn (Team $team): array => $this->serializeTeam($team))
            ->all();
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
