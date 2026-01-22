<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_initialize_league_and_play_a_week(): void
    {
        $initResponse = $this->postJson('/api/league/init');
        $initResponse->assertOk();
        $initResponse->assertJsonStructure([
            'season' => ['id', 'name', 'currentWeek', 'isFinished'],
            'currentWeek',
            'weeks',
            'standings',
            'prediction',
        ]);

        $playResponse = $this->postJson('/api/league/play-week');
        $playResponse->assertOk();

        $payload = $playResponse->json();
        $this->assertSame(2, $payload['currentWeek']);

        $weekOne = collect($payload['weeks'])->firstWhere('number', 1);
        $this->assertTrue($weekOne['isPlayed']);
        $this->assertNotNull($weekOne['matches'][0]['homeScore']);
    }
}
