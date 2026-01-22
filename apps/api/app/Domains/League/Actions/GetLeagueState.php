<?php

namespace App\Domains\League\Actions;

use App\Domains\League\DTO\LeagueStateDTO;
use App\Domains\League\Models\Season;
use App\Domains\League\Services\ChampionshipPredictor;

class GetLeagueState
{
    public function __construct(private readonly ChampionshipPredictor $predictor)
    {
    }

    public function execute(?Season $season = null): LeagueStateDTO
    {
        $season = $season ?? Season::query()->latest('id')->firstOrFail();
        $season->loadMissing([
            'weeks.matches.homeTeam',
            'weeks.matches.awayTeam',
            'standings.team',
        ]);

        $prediction = null;
        if ($season->is_finished) {
            $prediction = $this->finalPrediction($season);
        } elseif ($this->shouldPredict($season)) {
            $prediction = $this->predictor->predict($season);
        }

        return LeagueStateDTO::fromSeason($season, $prediction);
    }

    private function shouldPredict(Season $season): bool
    {
        $remainingWeeks = $season->weeks->where('is_played', false)->count();

        return $remainingWeeks > 0 && $remainingWeeks <= 3;
    }

    /**
     * @return array<int, float>
     */
    private function finalPrediction(Season $season): array
    {
        $sorted = $season->standings
            ->sort(function ($a, $b): int {
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
            })
            ->values();

        $prediction = [];
        foreach ($sorted as $index => $standing) {
            $prediction[$standing->team_id] = $index === 0 ? 100.0 : 0.0;
        }

        return $prediction;
    }
}
