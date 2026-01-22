<?php

namespace Tests\Unit;

use App\Domains\League\Actions\EditMatchResult;
use App\Domains\League\Actions\GetAllTeams;
use App\Domains\League\Actions\GetDefaultTeams;
use App\Domains\League\Actions\GetFixtures;
use App\Domains\League\Actions\GetLeagueState;
use App\Domains\League\Actions\GetMatchById;
use App\Domains\League\Actions\GetTeamById;
use App\Domains\League\Actions\InitializeLeague;
use App\Domains\League\Actions\PlayAll;
use App\Domains\League\Actions\PlayWeek;
use App\Domains\League\Actions\ResetLeague;
use App\Domains\League\Actions\UpdateTeam;
use App\Domains\League\Models\LeagueMatch;
use App\Domains\League\Models\Season;
use App\Domains\League\Models\Standing;
use App\Domains\League\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LeagueActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_initialize_league_creates_core_records(): void
    {
        $action = app(InitializeLeague::class);
        $state = $action->execute();

        $this->assertDatabaseCount('seasons', 1);
        $this->assertDatabaseCount('teams', 4);
        $this->assertDatabaseCount('weeks', 6);
        $this->assertDatabaseCount('matches', 12);
        $this->assertDatabaseCount('standings', 4);

        $this->assertSame(4, count($state->toArray()['standings']));
    }

    public function test_play_week_marks_matches_and_advances_week(): void
    {
        $init = app(InitializeLeague::class);
        $init->execute();

        $action = app(PlayWeek::class);
        $state = $action->execute();

        $this->assertSame(2, $state->toArray()['currentWeek']);
        $this->assertDatabaseHas('weeks', ['number' => 1, 'is_played' => 1]);
        $this->assertDatabaseCount('matches', 12);
        $this->assertGreaterThan(0, LeagueMatch::query()->where('is_played', true)->count());
    }

    public function test_play_all_finishes_season(): void
    {
        $init = app(InitializeLeague::class);
        $init->execute();

        $action = app(PlayAll::class);
        $state = $action->execute();

        $this->assertTrue($state->toArray()['season']['isFinished']);
        $this->assertDatabaseHas('seasons', ['is_finished' => 1]);
    }

    public function test_edit_match_result_recalculates_standings(): void
    {
        $init = app(InitializeLeague::class);
        $init->execute();

        $match = LeagueMatch::query()->firstOrFail();
        $action = app(EditMatchResult::class);
        $state = $action->execute($match, 3, 0);

        $homeStanding = Standing::query()->where('team_id', $match->home_team_id)->firstOrFail();
        $awayStanding = Standing::query()->where('team_id', $match->away_team_id)->firstOrFail();

        $this->assertSame(1, $homeStanding->played);
        $this->assertSame(3, $homeStanding->points);
        $this->assertSame(0, $awayStanding->points);
        $this->assertSame($state->toArray()['currentWeek'], Season::query()->firstOrFail()->current_week);
    }

    public function test_get_league_state_returns_final_prediction_when_finished(): void
    {
        $season = Season::create(['name' => 'Final']);
        $teamA = Team::create(['season_id' => $season->id, 'name' => 'A', 'power' => 80]);
        $teamB = Team::create(['season_id' => $season->id, 'name' => 'B', 'power' => 70]);

        Standing::create(['season_id' => $season->id, 'team_id' => $teamA->id, 'points' => 6]);
        Standing::create(['season_id' => $season->id, 'team_id' => $teamB->id, 'points' => 3]);

        $season->update(['is_finished' => true]);

        $action = app(GetLeagueState::class);
        $state = $action->execute($season)->toArray();

        $this->assertSame(100.0, $state['prediction'][$teamA->id]);
        $this->assertSame(0.0, $state['prediction'][$teamB->id]);
    }

    public function test_get_fixtures_includes_bye_teams_for_odd_team_count(): void
    {
        $init = app(InitializeLeague::class);
        $teams = [
            ['name' => 'A', 'power' => 80],
            ['name' => 'B', 'power' => 70],
            ['name' => 'C', 'power' => 65],
            ['name' => 'D', 'power' => 60],
            ['name' => 'E', 'power' => 55],
        ];
        $init->execute($teams, 'Odd Season');

        $action = app(GetFixtures::class);
        $weeks = $action->execute();

        $this->assertNotEmpty($weeks[0]['byeTeams']);
        $this->assertSame(10, count($weeks));
    }

    public function test_reset_league_clears_all_tables(): void
    {
        $init = app(InitializeLeague::class);
        $init->execute();

        $action = app(ResetLeague::class);
        $action->execute();

        $this->assertDatabaseCount('seasons', 0);
        $this->assertDatabaseCount('teams', 0);
        $this->assertDatabaseCount('weeks', 0);
        $this->assertDatabaseCount('matches', 0);
        $this->assertDatabaseCount('standings', 0);
    }

    public function test_get_all_teams_returns_serialized_list(): void
    {
        $init = app(InitializeLeague::class);
        $init->execute();

        $action = app(GetAllTeams::class);
        $teams = $action->execute();

        $this->assertCount(4, $teams);
        $this->assertArrayHasKey('name', $teams[0]);
    }

    public function test_get_team_by_id_returns_matches(): void
    {
        $init = app(InitializeLeague::class);
        $init->execute();

        $team = Team::query()->firstOrFail();
        $action = app(GetTeamById::class);
        $payload = $action->execute($team);

        $this->assertArrayHasKey('team', $payload);
        $this->assertArrayHasKey('matches', $payload);
        $this->assertNotEmpty($payload['matches']);
    }

    public function test_update_team_blocks_name_change_after_start(): void
    {
        $init = app(InitializeLeague::class);
        $init->execute();

        $season = Season::query()->firstOrFail();
        $season->update(['current_week' => 2]);

        $team = Team::query()->firstOrFail();
        $action = app(UpdateTeam::class);

        $this->expectException(ValidationException::class);
        $action->execute($team, ['name' => 'Renamed']);
    }

    public function test_update_team_allows_power_change(): void
    {
        $init = app(InitializeLeague::class);
        $init->execute();

        $team = Team::query()->firstOrFail();
        $action = app(UpdateTeam::class);
        $payload = $action->execute($team, ['power' => 90]);

        $this->assertSame(90, $payload['power']);
    }

    public function test_get_default_teams_returns_defaults(): void
    {
        $action = app(GetDefaultTeams::class);
        $teams = $action->execute();

        $this->assertCount(4, $teams);
    }

    public function test_get_match_by_id_returns_detail(): void
    {
        $init = app(InitializeLeague::class);
        $init->execute();

        $match = LeagueMatch::query()->firstOrFail();
        $action = app(GetMatchById::class);
        $payload = $action->execute($match);

        $this->assertSame($match->id, $payload['id']);
        $this->assertNotNull($payload['homeTeam']);
    }
}
