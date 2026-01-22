<?php

namespace App\Domains\League\Services;

use App\Domains\League\Models\Season;
use App\Domains\League\Models\Standing;
use Illuminate\Support\Collection;

class StandingsCalculator
{
    /**
     * @return Collection<int, Standing>
     */
    public function recalculate(Season $season): Collection
    {
        $season->loadMissing(['standings.team', 'matches']);

        $standings = $season->standings->keyBy('team_id');

        foreach ($standings as $standing) {
            $standing->fill([
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'gf' => 0,
                'ga' => 0,
                'gd' => 0,
                'points' => 0,
            ]);
        }

        foreach ($season->matches->where('is_played', true) as $match) {
            $home = $standings[$match->home_team_id];
            $away = $standings[$match->away_team_id];

            $homeGoals = (int) $match->home_score;
            $awayGoals = (int) $match->away_score;

            $home->played++;
            $away->played++;
            $home->gf += $homeGoals;
            $home->ga += $awayGoals;
            $away->gf += $awayGoals;
            $away->ga += $homeGoals;

            if ($homeGoals > $awayGoals) {
                $home->won++;
                $home->points += 3;
                $away->lost++;
            } elseif ($homeGoals < $awayGoals) {
                $away->won++;
                $away->points += 3;
                $home->lost++;
            } else {
                $home->drawn++;
                $away->drawn++;
                $home->points += 1;
                $away->points += 1;
            }
        }

        foreach ($standings as $standing) {
            $standing->gd = $standing->gf - $standing->ga;
            $standing->save();
        }

        return $standings->values()->sort(function (Standing $a, Standing $b): int {
            if ($a->points !== $b->points) {
                return $b->points <=> $a->points;
            }
            if ($a->gd !== $b->gd) {
                return $b->gd <=> $a->gd;
            }
            if ($a->gf !== $b->gf) {
                return $b->gf <=> $a->gf;
            }

            return strcmp($a->team->name, $b->team->name);
        })->values();
    }
}
