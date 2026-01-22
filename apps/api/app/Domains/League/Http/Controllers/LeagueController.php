<?php

namespace App\Domains\League\Http\Controllers;

use App\Domains\League\Actions\GetFixtures;
use App\Domains\League\Actions\GetLeagueState;
use App\Domains\League\Actions\InitializeLeague;
use App\Domains\League\Actions\PlayAll;
use App\Domains\League\Actions\PlayWeek;
use App\Domains\League\Actions\ResetLeague;
use App\Domains\League\Http\Requests\InitLeagueRequest;
use App\Domains\League\Http\Requests\PlayWeekRequest;
use Illuminate\Http\JsonResponse;

class LeagueController
{
    public function __construct(
        private readonly InitializeLeague $initializeLeague,
        private readonly PlayWeek $playWeek,
        private readonly PlayAll $playAll,
        private readonly GetLeagueState $getLeagueState,
        private readonly GetFixtures $getFixtures,
        private readonly ResetLeague $resetLeague,
    ) {
    }

    public function init(InitLeagueRequest $request): JsonResponse
    {
        $payload = $this->initializeLeague->execute(
            $request->input('teams'),
            $request->input('name')
        );

        return response()->json($payload->toArray());
    }

    public function playWeek(PlayWeekRequest $request): JsonResponse
    {
        $payload = $this->playWeek->execute();

        return response()->json($payload->toArray());
    }

    public function playAll(): JsonResponse
    {
        $payload = $this->playAll->execute();

        return response()->json($payload->toArray());
    }

    public function state(): JsonResponse
    {
        $payload = $this->getLeagueState->execute();

        return response()->json($payload->toArray());
    }

    public function fixtures(): JsonResponse
    {
        return response()->json([
            'weeks' => $this->getFixtures->execute(),
        ]);
    }

    public function reset(): JsonResponse
    {
        $this->resetLeague->execute();

        return response()->json(['status' => 'ok']);
    }
}
