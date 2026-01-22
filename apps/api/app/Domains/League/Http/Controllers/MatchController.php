<?php

namespace App\Domains\League\Http\Controllers;

use App\Domains\League\Actions\EditMatchResult;
use App\Domains\League\Actions\GetMatchById;
use App\Domains\League\Http\Requests\EditMatchRequest;
use App\Domains\League\Models\LeagueMatch;
use Illuminate\Http\JsonResponse;

class MatchController
{
    public function __construct(
        private readonly GetMatchById $getMatchById,
        private readonly EditMatchResult $editMatchResult,
    ) {
    }

    public function show(LeagueMatch $match): JsonResponse
    {
        return response()->json($this->getMatchById->execute($match));
    }

    public function edit(EditMatchRequest $request, LeagueMatch $match): JsonResponse
    {
        $payload = $this->editMatchResult->execute(
            $match,
            (int) $request->input('home_score'),
            (int) $request->input('away_score')
        );

        return response()->json($payload->toArray());
    }
}
