<?php

namespace Tests\Unit;

use App\Domains\League\Models\Season;
use App\Domains\League\Models\Team;
use App\Domains\League\Services\FixtureGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FixtureGeneratorTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('teamCountsProvider')]
    public function test_double_round_robin_pairs_are_generated(int $teamCount): void
    {
        $season = Season::create(['name' => 'Fixture Test']);
        $teams = collect();

        for ($i = 0; $i < $teamCount; $i++) {
            $teams->push(Team::create([
                'season_id' => $season->id,
                'name' => 'Team '.$i,
                'power' => 50 + $i,
            ]));
        }

        $generator = app(FixtureGenerator::class);
        $generator->generate($season, $teams);

        $expectedWeeks = $teamCount % 2 === 0 ? ($teamCount - 1) * 2 : $teamCount * 2;
        $expectedMatches = $teamCount * ($teamCount - 1);

        $this->assertDatabaseCount('weeks', $expectedWeeks);
        $this->assertDatabaseCount('matches', $expectedMatches);

        $matches = $season->matches()->get();
        $pairCounts = [];

        foreach ($matches as $match) {
            $pair = [$match->home_team_id, $match->away_team_id];
            sort($pair);
            $key = $pair[0].'-'.$pair[1];
            $pairCounts[$key] = ($pairCounts[$key] ?? 0) + 1;
        }

        $this->assertCount(intval($teamCount * ($teamCount - 1) / 2), $pairCounts);

        foreach ($pairCounts as $count) {
            $this->assertSame(2, $count);
        }
    }

    public static function teamCountsProvider(): array
    {
        return [
            [4],
            [5],
            [6],
            [7],
        ];
    }
}
