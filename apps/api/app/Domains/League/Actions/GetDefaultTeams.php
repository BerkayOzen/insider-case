<?php

namespace App\Domains\League\Actions;

use App\Domains\League\Services\DefaultTeamsProvider;

class GetDefaultTeams
{
    public function __construct(private readonly DefaultTeamsProvider $defaultTeamsProvider)
    {
    }

    /**
     * @return array<int, array{name:string, power:int}>
     */
    public function execute(): array
    {
        return $this->defaultTeamsProvider->getDefaultTeams();
    }
}
