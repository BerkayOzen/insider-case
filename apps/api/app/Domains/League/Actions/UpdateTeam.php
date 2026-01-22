<?php

namespace App\Domains\League\Actions;

use App\Domains\League\Models\Team;
use Illuminate\Validation\ValidationException;

class UpdateTeam
{
    /**
     * @param array{name?:string, power?:int} $payload
     * @return array<string, mixed>
     */
    public function execute(Team $team, array $payload): array
    {
        $team->loadMissing('season.weeks');

        $seasonStarted = $team->season
            && ($team->season->current_week > 1 
            || $team->season->weeks->where('is_played', true)->isNotEmpty());

        if ($seasonStarted && array_key_exists('name', $payload)) {
            throw ValidationException::withMessages([
                'name' => 'Season started. Team name cannot be changed.',
            ]);
        }

        $team->fill($payload);
        $team->save();

        return $this->serializeTeam($team);
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
