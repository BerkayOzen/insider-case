<?php

namespace App\Domains\League\Actions;

use App\Domains\League\DTO\LeagueStateDTO;
use App\Domains\League\Models\Season;
use App\Domains\League\Services\MatchSimulator;
use App\Domains\League\Services\StandingsCalculator;
use Illuminate\Support\Facades\DB;

class PlayWeek
{
    public function __construct(
        private readonly MatchSimulator $simulator,
        private readonly StandingsCalculator $standingsCalculator,
        private readonly GetLeagueState $getLeagueState,
    ) {
    }

    public function execute(?Season $season = null): LeagueStateDTO
    {
        return DB::transaction(function () use ($season): LeagueStateDTO {
            $season = $season ?? Season::query()->latest('id')->firstOrFail();

            if ($season->is_finished) {
                return $this->getLeagueState->execute($season);
            }

            $week = $season->weeks()->where('number', $season->current_week)->first();
            if (! $week) {
                $season->is_finished = true;
                $season->save();

                return $this->getLeagueState->execute($season);
            }

            $week->loadMissing('matches.homeTeam', 'matches.awayTeam');

            foreach ($week->matches as $match) {
                if (! $match->is_played) {
                    $this->simulator->simulateMatch($match);
                }
            }

            $week->is_played = true;
            $week->save();

            $this->standingsCalculator->recalculate($season);

            if ($season->current_week >= 6) {
                $season->is_finished = true;
            } else {
                $season->current_week++;
            }
            $season->save();

            return $this->getLeagueState->execute($season);
        });
    }
}
