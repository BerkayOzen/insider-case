<?php

namespace App\Domains\League\Services;

use App\Domains\League\Models\LeagueMatch;
use App\Domains\League\Models\Team;
use App\Support\Random\RandomGenerator;

class MatchSimulator
{
    public function __construct(private readonly RandomGenerator $random)
    {
    }

    /**
     * @return array{0:int,1:int}
     */
    public function simulateScores(Team $homeTeam, Team $awayTeam): array
    {
        $homeRating = $homeTeam->power + 5;
        $awayRating = $awayTeam->power;
        $total = max(1, $homeRating + $awayRating);
        $homeShare = $homeRating / $total;

        $homeExpected = 0.8 + (2.2 * $homeShare);
        $awayExpected = 0.8 + (2.2 * (1 - $homeShare));

        $homeGoals = $this->clampGoals($this->poisson($homeExpected));
        $awayGoals = $this->clampGoals($this->poisson($awayExpected));

        return [$homeGoals, $awayGoals];
    }

    public function simulateMatch(LeagueMatch $match): LeagueMatch
    {
        [$homeScore, $awayScore] = $this->simulateScores($match->homeTeam, $match->awayTeam);

        $match->fill([
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'is_played' => true,
        ]);
        $match->save();

        return $match;
    }

    private function poisson(float $lambda): int
    {
        $limit = exp(-$lambda);
        $product = 1.0;
        $k = 0;

        do {
            $k++;
            $product *= $this->random->float();
        } while ($product > $limit);

        return $k - 1;
    }

    private function clampGoals(int $goals): int
    {
        return max(0, min(4, $goals));
    }
}
