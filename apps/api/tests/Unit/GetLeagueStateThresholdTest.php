<?php

namespace Tests\Unit;

use App\Domains\League\Actions\GetLeagueState;
use App\Domains\League\Models\Season;
use App\Domains\League\Models\Standing;
use App\Domains\League\Models\Team;
use App\Domains\League\Models\Week;
use App\Domains\League\Services\ChampionshipPredictor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetLeagueStateThresholdTest extends TestCase
{
    use RefreshDatabase;

    public function test_prediction_is_returned_when_three_weeks_remaining(): void
    {
        $season = $this->seedSeasonWithWeeks(6, 3);

        $predictor = $this->mock(ChampionshipPredictor::class);
        $predictor->shouldReceive('predict')->andReturn([$season->teams()->first()->id => 99.0]);

        $action = app(GetLeagueState::class);
        $state = $action->execute($season)->toArray();

        $this->assertNotNull($state['prediction']);
    }

    public function test_prediction_is_not_returned_when_four_weeks_remaining(): void
    {
        $season = $this->seedSeasonWithWeeks(6, 2);

        $predictor = $this->mock(ChampionshipPredictor::class);
        $predictor->shouldReceive('predict')->andReturn([$season->teams()->first()->id => 99.0]);

        $action = app(GetLeagueState::class);
        $state = $action->execute($season)->toArray();

        $this->assertNull($state['prediction']);
    }

    private function seedSeasonWithWeeks(int $totalWeeks, int $playedWeeks): Season
    {
        $season = Season::create(['name' => 'Threshold Test']);

        $teams = collect();
        for ($i = 0; $i < 4; $i++) {
            $teams->push(Team::create([
                'season_id' => $season->id,
                'name' => 'Team '.$i,
                'power' => 60,
            ]));
        }

        foreach ($teams as $team) {
            Standing::create([
                'season_id' => $season->id,
                'team_id' => $team->id,
            ]);
        }

        for ($week = 1; $week <= $totalWeeks; $week++) {
            Week::create([
                'season_id' => $season->id,
                'number' => $week,
                'is_played' => $week <= $playedWeeks,
            ]);
        }

        return $season->fresh();
    }
}
