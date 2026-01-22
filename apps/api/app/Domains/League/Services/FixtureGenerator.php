<?php

namespace App\Domains\League\Services;

use App\Domains\League\Models\LeagueMatch;
use App\Domains\League\Models\Season;
use App\Domains\League\Models\Team;
use App\Domains\League\Models\Week;
use Illuminate\Support\Collection;

class FixtureGenerator
{
    /**
     * @param Collection<int, Team> $teams
     */
    public function generate(Season $season, Collection $teams): void
    {
        $teamList = $teams->values()->all();
        $teamCount = count($teamList);

        if ($teamCount < 2) {
            return;
        }

        $rotation = $teamList;
        if ($teamCount % 2 !== 0) {
            $rotation[] = null;
            $teamCount++;
        }

        $roundCount = $teamCount - 1;
        $totalWeeks = $roundCount * 2;
        $weeks = collect();

        for ($week = 1; $week <= $totalWeeks; $week++) {
            $weeks[$week] = Week::create([
                'season_id' => $season->id,
                'number' => $week,
                'is_played' => false,
            ]);
        }

        for ($round = 0; $round < $roundCount; $round++) {
            $weekNumber = $round + 1;
            $reverseWeekNumber = $round + 1 + $roundCount;

            for ($i = 0; $i < ($teamCount / 2); $i++) {
                $home = $rotation[$i];
                $away = $rotation[$teamCount - 1 - $i];

                if ($home === null || $away === null) {
                    continue;
                }

                $homeTeam = $home;
                $awayTeam = $away;

                if ($round % 2 === 1) {
                    [$homeTeam, $awayTeam] = [$awayTeam, $homeTeam];
                }

                LeagueMatch::create([
                    'season_id' => $season->id,
                    'week_id' => $weeks[$weekNumber]->id,
                    'home_team_id' => $homeTeam->id,
                    'away_team_id' => $awayTeam->id,
                ]);

                LeagueMatch::create([
                    'season_id' => $season->id,
                    'week_id' => $weeks[$reverseWeekNumber]->id,
                    'home_team_id' => $awayTeam->id,
                    'away_team_id' => $homeTeam->id,
                ]);
            }

            $rotation = $this->rotateTeams($rotation);
        }
    }

    /**
     * @param array<int, Team|null> $teams
     * @return array<int, Team|null>
     */
    private function rotateTeams(array $teams): array
    {
        $fixed = array_shift($teams);
        $moved = array_pop($teams);
        array_unshift($teams, $moved);
        array_unshift($teams, $fixed);

        return $teams;
    }
}
