<?php

namespace App\Domains\League\Actions;

use App\Domains\League\DTO\LeagueStateDTO;
use App\Domains\League\Models\LeagueMatch;
use App\Domains\League\Services\StandingsCalculator;
use Illuminate\Support\Facades\DB;

class EditMatchResult
{
    public function __construct(
        private readonly StandingsCalculator $standingsCalculator,
        private readonly GetLeagueState $getLeagueState,
    ) {
    }

    public function execute(LeagueMatch $match, int $homeScore, int $awayScore): LeagueStateDTO
    {
        return DB::transaction(function () use ($match, $homeScore, $awayScore): LeagueStateDTO {
            $match->fill([
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'is_played' => true,
            ]);
            $match->save();

            $match->loadMissing('week');
            if ($match->week && $match->week->matches()->where('is_played', false)->count() === 0) {
                $match->week->is_played = true;
                $match->week->save();
            }

            $season = $match->season;
            $this->standingsCalculator->recalculate($season);

            return $this->getLeagueState->execute($season);
        });
    }
}
