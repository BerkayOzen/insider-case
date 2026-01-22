<?php

namespace App\Domains\League\Services;

use App\Domains\League\Models\Season;

class ChampionshipPredictor
{
    private const ITERATIONS = 2000;

    public function __construct(private readonly MatchSimulator $simulator)
    {
    }

    /**
     * @return array<int, float>|null
     */
    public function predict(Season $season): ?array
    {
        $season->loadMissing(['teams', 'standings', 'matches.homeTeam', 'matches.awayTeam']);

        $remaining = $season->matches->where('is_played', false);
        if ($remaining->isEmpty()) {
            return null;
        }

        $teams = $season->teams->keyBy('id');
        $standings = $season->standings->keyBy('team_id');

        $base = [];
        foreach ($teams as $teamId => $team) {
            $standing = $standings[$teamId] ?? null;
            $base[$teamId] = [
                'team_id' => $teamId,
                'name' => $team->name,
                'played' => $standing?->played ?? 0,
                'won' => $standing?->won ?? 0,
                'drawn' => $standing?->drawn ?? 0,
                'lost' => $standing?->lost ?? 0,
                'gf' => $standing?->gf ?? 0,
                'ga' => $standing?->ga ?? 0,
                'gd' => $standing?->gd ?? 0,
                'points' => $standing?->points ?? 0,
            ];
        }

        $wins = array_fill_keys(array_keys($base), 0);

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $table = $this->cloneTable($base);

            foreach ($remaining as $match) {
                $homeTeam = $teams[$match->home_team_id];
                $awayTeam = $teams[$match->away_team_id];

                [$homeGoals, $awayGoals] = $this->simulator->simulateScores($homeTeam, $awayTeam);
                $this->applyResult($table, $match->home_team_id, $match->away_team_id, $homeGoals, $awayGoals);
            }

            $winner = $this->determineChampion($table);
            $wins[$winner['team_id']]++;
        }

        $prediction = [];
        foreach ($wins as $teamId => $count) {
            $prediction[$teamId] = round(($count / self::ITERATIONS) * 100, 2);
        }

        return $prediction;
    }

    /**
     * @param array<int, array<string, int|string>> $table
     */
    private function applyResult(array &$table, int $homeId, int $awayId, int $homeGoals, int $awayGoals): void
    {
        $table[$homeId]['played']++;
        $table[$awayId]['played']++;
        $table[$homeId]['gf'] += $homeGoals;
        $table[$homeId]['ga'] += $awayGoals;
        $table[$awayId]['gf'] += $awayGoals;
        $table[$awayId]['ga'] += $homeGoals;

        if ($homeGoals > $awayGoals) {
            $table[$homeId]['won']++;
            $table[$homeId]['points'] += 3;
            $table[$awayId]['lost']++;
        } elseif ($homeGoals < $awayGoals) {
            $table[$awayId]['won']++;
            $table[$awayId]['points'] += 3;
            $table[$homeId]['lost']++;
        } else {
            $table[$homeId]['drawn']++;
            $table[$awayId]['drawn']++;
            $table[$homeId]['points'] += 1;
            $table[$awayId]['points'] += 1;
        }

        $table[$homeId]['gd'] = $table[$homeId]['gf'] - $table[$homeId]['ga'];
        $table[$awayId]['gd'] = $table[$awayId]['gf'] - $table[$awayId]['ga'];
    }

    /**
     * @param array<int, array<string, int|string>> $table
     * @return array<string, int|string>
     */
    private function determineChampion(array $table): array
    {
        $rows = array_values($table);

        usort($rows, function (array $a, array $b): int {
            if ($a['points'] !== $b['points']) {
                return $b['points'] <=> $a['points'];
            }
            if ($a['gd'] !== $b['gd']) {
                return $b['gd'] <=> $a['gd'];
            }
            if ($a['gf'] !== $b['gf']) {
                return $b['gf'] <=> $a['gf'];
            }

            return strcmp((string) $a['name'], (string) $b['name']);
        });

        return $rows[0];
    }

    /**
     * @param array<int, array<string, int|string>> $base
     * @return array<int, array<string, int|string>>
     */
    private function cloneTable(array $base): array
    {
        $copy = [];
        foreach ($base as $teamId => $row) {
            $copy[$teamId] = $row;
        }

        return $copy;
    }
}
