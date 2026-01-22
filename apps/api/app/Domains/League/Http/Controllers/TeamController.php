<?php

namespace App\Domains\League\Http\Controllers;

use App\Domains\League\Actions\GetAllTeams;
use App\Domains\League\Actions\GetDefaultTeams;
use App\Domains\League\Actions\GetTeamById;
use App\Domains\League\Actions\UpdateTeam;
use App\Domains\League\Http\Requests\UpdateTeamRequest;
use App\Domains\League\Models\Team;
use Illuminate\Http\JsonResponse;

class TeamController
{
    public function __construct(
        private readonly GetAllTeams $getAllTeams,
        private readonly GetTeamById $getTeamById,
        private readonly UpdateTeam $updateTeam,
        private readonly GetDefaultTeams $getDefaultTeams,
    ) {
    }

    public function getAll(): JsonResponse
    {
        return response()->json($this->getAllTeams->execute());
    }

    public function getById(Team $team): JsonResponse
    {
        return response()->json($this->getTeamById->execute($team));
    }

    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        return response()->json($this->updateTeam->execute($team, $request->validated()));
    }

    public function getDefaultTeams(): JsonResponse
    {
        return response()->json($this->getDefaultTeams->execute());
    }
}
