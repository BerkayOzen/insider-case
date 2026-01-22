<?php

namespace App\Domains\League\Actions;

use App\Domains\League\DTO\LeagueStateDTO;
use App\Domains\League\Models\Season;
use Illuminate\Support\Facades\DB;

class PlayAll
{
    public function __construct(private readonly PlayWeek $playWeek)
    {
    }

    public function execute(?Season $season = null): LeagueStateDTO
    {
        return DB::transaction(function () use ($season): LeagueStateDTO {
            $season = $season ?? Season::query()->latest('id')->firstOrFail();
            $state = null;

            for ($i = 0; $i < 6 && ! $season->is_finished; $i++) {
                $state = $this->playWeek->execute($season);
                $season->refresh();
            }

            return $state ?? $this->playWeek->execute($season);
        });
    }
}
