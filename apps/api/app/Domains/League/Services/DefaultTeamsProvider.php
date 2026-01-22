<?php

namespace App\Domains\League\Services;

class DefaultTeamsProvider
{
    /**
     * @return array<int, array{name:string, power:int}>
     */
    public function getDefaultTeams(): array
    {
        return [
            ['name' => 'Lions', 'power' => 85],
            ['name' => 'Tigers', 'power' => 78],
            ['name' => 'Eagles', 'power' => 72],
            ['name' => 'Sharks', 'power' => 68],
        ];
    }
}
