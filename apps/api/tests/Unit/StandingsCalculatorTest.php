<?php

namespace Tests\Unit;

use App\Domains\League\Models\LeagueMatch;
use App\Domains\League\Models\Season;
use App\Domains\League\Models\Standing;
use App\Domains\League\Models\Team;
use App\Domains\League\Models\Week;
use App\Domains\League\Services\StandingsCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StandingsCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_standings_points_are_calculated_correctly(): void
    {
        $season = Season::create(['name' => 'Test Season']);

        $teamA = Team::create(['season_id' => $season->id, 'name' => 'Alpha', 'power' => 80]);
        $teamB = Team::create(['season_id' => $season->id, 'name' => 'Beta', 'power' => 75]);

        $week1 = Week::create(['season_id' => $season->id, 'number' => 1]);
        $week2 = Week::create(['season_id' => $season->id, 'number' => 2]);

        LeagueMatch::create([
            'season_id' => $season->id,
            'week_id' => $week1->id,
            'home_team_id' => $teamA->id,
            'away_team_id' => $teamB->id,
            'home_score' => 2,
            'away_score' => 0,
            'is_played' => true,
        ]);

        LeagueMatch::create([
            'season_id' => $season->id,
            'week_id' => $week2->id,
            'home_team_id' => $teamB->id,
            'away_team_id' => $teamA->id,
            'home_score' => 1,
            'away_score' => 1,
            'is_played' => true,
        ]);

        foreach ([$teamA, $teamB] as $team) {
            Standing::create(['season_id' => $season->id, 'team_id' => $team->id]);
        }

        $calculator = app(StandingsCalculator::class);
        $standings = $calculator->recalculate($season)->keyBy('team_id');

        $alpha = $standings[$teamA->id];
        $beta = $standings[$teamB->id];

        $this->assertSame(2, $alpha->played);
        $this->assertSame(1, $alpha->won);
        $this->assertSame(1, $alpha->drawn);
        $this->assertSame(0, $alpha->lost);
        $this->assertSame(3, $alpha->gf);
        $this->assertSame(1, $alpha->ga);
        $this->assertSame(2, $alpha->gd);
        $this->assertSame(4, $alpha->points);

        $this->assertSame(2, $beta->played);
        $this->assertSame(0, $beta->won);
        $this->assertSame(1, $beta->drawn);
        $this->assertSame(1, $beta->lost);
        $this->assertSame(1, $beta->gf);
        $this->assertSame(3, $beta->ga);
        $this->assertSame(-2, $beta->gd);
        $this->assertSame(1, $beta->points);
    }
}
