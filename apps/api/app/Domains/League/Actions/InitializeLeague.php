<?php

namespace App\Domains\League\Actions;

use App\Domains\League\DTO\LeagueStateDTO;
use App\Domains\League\Models\Season;
use App\Domains\League\Models\Standing;
use App\Domains\League\Models\Team;
use App\Domains\League\Services\DefaultTeamsProvider;
use App\Domains\League\Services\FixtureGenerator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class InitializeLeague
{
    public function __construct(
        private readonly FixtureGenerator $fixtureGenerator,
        private readonly GetLeagueState $getLeagueState,
        private readonly DefaultTeamsProvider $defaultTeamsProvider,
    ) {
    }

    /**
     * @param array<int, array{name:string, power:int}>|null $teams
     */
    public function execute(?array $teams = null, ?string $seasonName = null): LeagueStateDTO
    {
        return DB::transaction(function () use ($teams, $seasonName): LeagueStateDTO {
            $season = Season::create([
                'name' => $seasonName ?? 'Season 1',
                'current_week' => 1,
                'is_finished' => false,
            ]);

            $teamData = $teams ?? $this->defaultTeamsProvider->getDefaultTeams();

            $teamModels = collect($teamData)->map(function (array $data) use ($season): Team {
                return Team::create([
                    'season_id' => $season->id,
                    'name' => Arr::get($data, 'name'),
                    'power' => Arr::get($data, 'power'),
                ]);
            });

            $this->fixtureGenerator->generate($season, $teamModels);

            foreach ($teamModels as $team) {
                Standing::create([
                    'season_id' => $season->id,
                    'team_id' => $team->id,
                ]);
            }

            return $this->getLeagueState->execute($season);
        });
    }

}
